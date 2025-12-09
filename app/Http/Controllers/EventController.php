<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EventController extends Controller
{
    protected GoogleCalendarService $googleCalendar;

    public function __construct(GoogleCalendarService $googleCalendar)
    {
        $this->googleCalendar = $googleCalendar;
    }

    /**
     * Lista de eventos del usuario autenticado.
     * Permite filtrar por mes (?month=2025-11).
     */
    public function index(Request $request)
    {
        $user  = $request->user();
        $month = $request->query('month');

        $query = Event::where('user_id', $user->id);

        if ($month) {
            try {
                $start = Carbon::parse($month . '-01')->startOfMonth();
                $end   = (clone $start)->endOfMonth();
                $query->whereBetween('start_at', [$start, $end]);
            } catch (\Throwable $e) {
                Log::warning('Filtro de mes inválido para eventos', [
                    'month' => $month,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $events = $query->orderBy('start_at', 'asc')->get();

        return Inertia::render('Events/Index', [
            'events'  => $events,
            'filters' => [
                'month' => $month,
            ],
        ]);
    }

    /**
     * Guardar un nuevo evento.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);

        // 🔹 Campos extra de recordatorio desde el formulario
        $data['reminder_type']      = $request->input('reminder_type');      // exact | before | recurrent
        $data['reminder_interval']  = $request->input('reminder_interval');  // número
        $data['reminder_unit']      = $request->input('reminder_unit');      // minutes | hours
        $data['reminder_start_at']  = $request->input('reminder_start_at');  // para recurrentes
        $data['reminder_frequency'] = $request->input('reminder_frequency'); // opcional

        // 🔹 Calcular reminder_at según el tipo
        $this->applyReminderAt($data, $request);

        $data['user_id'] = $request->user()->id;

        $event = Event::create($data);

        // 🔌 Sincronizar con Google Calendar si el usuario está conectado
        if ($request->user()->google_access_token) {
            $googleId = $this->googleCalendar->createEvent($event);
            if ($googleId) {
                $event->google_event_id = $googleId;
                $event->save();
            }
        }

        return redirect()
            ->route('events.index')
            ->with('success', 'Evento creado correctamente.');
    }

    /**
     * Actualizar un evento existente.
     */
    public function update(Request $request, Event $event)
    {
        $this->authorizeEvent($request, $event);

        $data = $this->validateData($request);

        $data['reminder_type']      = $request->input('reminder_type');
        $data['reminder_interval']  = $request->input('reminder_interval');
        $data['reminder_unit']      = $request->input('reminder_unit');
        $data['reminder_start_at']  = $request->input('reminder_start_at');
        $data['reminder_frequency'] = $request->input('reminder_frequency');

        // 🔹 Recalcular reminder_at si aplica
        $this->applyReminderAt($data, $request);

        $event->update($data);

        // 🔌 Actualizar en Google Calendar si está conectado
        if ($request->user()->google_access_token) {
            if ($event->google_event_id) {
                $this->googleCalendar->updateEvent($event);
            } else {
                $googleId = $this->googleCalendar->createEvent($event);
                if ($googleId) {
                    $event->google_event_id = $googleId;
                    $event->save();
                }
            }
        }

        return redirect()
            ->route('events.index')
            ->with('success', 'Evento actualizado correctamente.');
    }

    /**
     * Eliminar un evento.
     */
    public function destroy(Request $request, Event $event)
    {
        $this->authorizeEvent($request, $event);

        if ($request->user()->google_access_token && $event->google_event_id) {
            $this->googleCalendar->deleteEvent($event);
        }

        $event->delete();

        return redirect()
            ->route('events.index')
            ->with('success', 'Evento eliminado correctamente.');
    }

    /**
     * Validación reutilizable para crear/editar.
     */
    protected function validateData(Request $request): array
    {
        return $request->validate([
            'title'            => ['required', 'string', 'max:150'],
            'description'      => ['nullable', 'string'],
            'type'             => ['required', 'string', 'max:50'],
            'location'         => ['nullable', 'string', 'max:150'],
            'start_at'         => ['required', 'date'],
            'end_at'           => ['nullable', 'date', 'after_or_equal:start_at'],
            'all_day'          => ['sometimes', 'boolean'],

            // Para tipo EXACT
            'reminder_at'      => ['nullable', 'date', 'before_or_equal:start_at'],

            // Canal: whatsapp | email | both
            'reminder_channel' => ['nullable', 'string', 'max:20'],

            // Tipo de recordatorio
            'reminder_type'    => ['required', 'string', 'in:exact,before,recurrent'],

            // Para BEFORE y RECURRENT
            'reminder_interval' => ['nullable', 'integer', 'min:1', 'required_if:reminder_type,before,recurrent'],
            'reminder_unit'     => ['nullable', 'string', 'in:minutes,hours', 'required_if:reminder_type,before,recurrent'],

            // Para RECURRENT
            'reminder_start_at' => ['nullable', 'date', 'required_if:reminder_type,recurrent'],

            'reminder_frequency'=> ['nullable', 'integer', 'min:1'],

            'status'           => ['required', 'string', 'max:20'],
        ]);
    }

    /**
     * Verifica que el evento pertenezca al usuario autenticado.
     */
    protected function authorizeEvent(Request $request, Event $event): void
    {
        if ($event->user_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para modificar este evento.');
        }
    }

    /**
     * Lógica para calcular y ajustar reminder_at según el tipo de recordatorio.
     *
     * - exact: usa el reminder_at que viene del form
     * - before: calcula start_at - intervalo
     * - recurrent: opcionalmente podemos usar reminder_start_at como primer reminder_at
     */
    protected function applyReminderAt(array &$data, Request $request): void
    {
        $type = $data['reminder_type'] ?? null;

        if ($type === 'before') {
            if (!empty($data['start_at']) &&
                !empty($data['reminder_interval']) &&
                !empty($data['reminder_unit'])
            ) {
                $start = Carbon::parse($data['start_at']);

                if ($data['reminder_unit'] === 'hours') {
                    $reminderAt = $start->copy()->subHours((int)$data['reminder_interval']);
                } else {
                    $reminderAt = $start->copy()->subMinutes((int)$data['reminder_interval']);
                }

                $data['reminder_at'] = $reminderAt;
            }
        } elseif ($type === 'recurrent') {
            // Para recurrente, usamos reminder_start_at como primer "próximo reminder_at"
            if (!empty($data['reminder_start_at'])) {
                $data['reminder_at'] = $data['reminder_start_at'];
            } elseif (!empty($data['start_at'])) {
                // fallback: si no hay reminder_start_at, usamos start_at (no ideal, pero evita null)
                $data['reminder_at'] = $data['start_at'];
            }
        }
        // Si es 'exact', se respeta el reminder_at que vino del form
    }
}
