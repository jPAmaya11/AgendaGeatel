<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Notebook;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notebooks = Notebook::where('owner_id', $user->id)
            ->orderBy('title')
            ->get();

        $ownNotes = Note::with('tags', 'notebook')
            ->where('owner_id', $user->id)
            ->get();

        $ownNotes->each(function ($note) {
            $note->is_owner       = true;
            $note->shared_with_me = false;
            $note->can_edit       = true;
        });

        $sharedNotes = $user->sharedNotes()
            ->with('tags', 'notebook', 'owner')
            ->get();

        $sharedNotes->each(function ($note) {
            $note->is_owner       = false;
            $note->shared_with_me = true;
            $note->can_edit       = $note->pivot && $note->pivot->permission === 'edit';
        });

        $notes = Note::with('tags', 'notebook', 'sharedWith')
        ->where('owner_id', $user->id)
        ->orWhereHas('sharedWith', fn ($q) =>
            $q->where('users.id', $user->id)
        )
        ->get()
        ->map(function ($note) use ($user) {
            return array_merge($note->toArray(), [
                'is_owner'       => $note->isOwner($user),
                'can_edit'       => $note->canEdit($user),
                'shared_with_me' => !$note->isOwner($user),
            ]);
        });

        $categories = Category::where('user_id', $user->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Notes/Index', [
            'notebooks'  => $notebooks,
            'notes'      => $notes,
            'authUser'   => $user,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'notebook_id' => ['nullable', 'exists:notebooks,id'],
            'title'       => ['nullable', 'string', 'max:255'],
            'category'    => ['nullable', 'string', 'max:100'],
            'content'     => ['nullable', 'string'],
        ]);

        $data['owner_id'] = $user->id;
        $data['position'] = Note::where('owner_id', $user->id)->max('position') + 1;

        $note = Note::create($data);

        return redirect()
            ->route('notes.index')
            ->with('success', 'Nota creada correctamente');
    }

    protected function canEdit(Note $note, User $user): bool
    {
        if ($note->owner_id === $user->id) {
            return true;
        }

        // revisar pivot can_edit
        return $note->sharedWith()
            ->where('users.id', $user->id)
            ->wherePivot('can_edit', true)
            ->exists();
    }

    public function update(Request $request, Note $note)
    {
        $user = $request->user();

        if (!$note->canEdit($user)) {
            abort(403, 'No tienes permiso para editar esta nota');
        }

        $data = $request->validate([
            'title'       => ['nullable', 'string', 'max:255'],
            'category'    => ['nullable', 'string', 'max:100'],
            'content'     => ['nullable', 'string'],
            'is_pinned'   => ['nullable', 'boolean'],
            'notebook_id' => ['nullable', 'exists:notebooks,id'],
        ]);

        $note->update($data);

        return back()->with('success', 'Nota actualizada');
    }

    public function destroy(Request $request, Note $note)
    {
        $user = $request->user();

        if ($note->owner_id !== $user->id) {
            abort(403, 'No puedes eliminar esta nota');
        }

        $note->delete();

        return back()->with('success', 'Nota eliminada');
    }

    // Mejorar nota con IA
    public function aiFormat(Note $note, Request $request)
    {
        $user = $request->user();

        if (!$note->canEdit($request->user())) {
            abort(403, 'No tienes permiso para modificar esta nota');
        }

        $data = $request->validate([
            'content' => ['required', 'string'],
            'title'   => ['nullable', 'string'],
        ]);

        $originalContent = $data['content'];
        $title = $data['title'] ?? $note->title;

        try {
            $apiKey = env('OPENAI_API_KEY');
            $model  = env('OPENAI_MODEL', 'deepseek/deepseek-chat-v3.1');

            if (!$apiKey) {
                return back()->with('aiMessage', 'No hay API KEY configurada para la IA.');
            }

            $prompt = <<<TXT
Eres un asistente que mejora notas personales.

Tarea:
- Reescribe la nota de manera clara, ordenada y formal.
- Respeta el idioma original (si está en español, responde en español).
- Mantén TODO el contenido importante (usuarios, contraseñas, datos, fechas, números).
- Usa viñetas o secciones si ayuda a la claridad.
- No agregues explicaciones fuera de la nota.

Estilo deseado:
- Sin texto en negrita, pero formato profesional.
- Listas con guiones ordenadas.
- Sin repeticiones innecesarias ni adornos exagerados.

Título: "{$title}"

Contenido de la nota:
{$originalContent}

Devuelve SOLO el contenido mejorado de la nota, sin comentarios adicionales.
TXT;

            $response = Http::withToken($apiKey)
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'Eres un asistente experto en redacción y organización de notas.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('Error al llamar a OpenRouter', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return back()->with('aiMessage', 'No se pudo procesar la nota con IA (error en el servicio).');
            }

            $improved = $response->json('choices.0.message.content') ?? $originalContent;

            // Guardar cambios en la nota
            $note->content = $improved;
            $note->save();

            return back()->with([
                'aiMessage'                => 'La nota fue mejorada con IA.',
                'selectedNoteIdFromServer' => $note->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Excepción al llamar a OpenAI', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('aiMessage', 'Ocurrió un error al usar la IA.');
        }
    }

    public function importFathom(Note $note, Request $request)
    {
        $user = $request->user();

        if (!$note->canEdit($user)) {
            abort(403);
        }

        $data = $request->validate([
            'fathom_url' => ['required', 'url'],
        ]);

        $note->update([
            'fathom_url' => $data['fathom_url'],
        ]);

        $apiKey = env('OPENAI_API_KEY');
        $model  = env('OPENAI_MODEL', 'deepseek/deepseek-chat-v3.1');

        $prompt = <<<PROMPT
    Tienes acceso al contenido de una página de Fathom que resume una reunión.

    Tarea:
    - Extrae únicamente la sección llamada "SUMMARY"
    - Muestra todo el contenido de la sección "SUMMARY" sin modificar ni agregar información.
    - Hazlo claro, profesional y bien estructurado
    - No inventes información
    - Muestra estrictamente la información de la sección llamada "SUMMARY" completa y tal como está.

    Link de Fathom:
    {$data['fathom_url']}

    Devuelve SOLO el contenido del SUMMARY.
    PROMPT;

        $response = Http::withToken($apiKey)
            ->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'Eres un asistente experto en análisis de reuniones.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if (!$response->successful()) {
            return back()->with('error', 'No se pudo importar el resumen de Fathom');
        }

        $summary = $response->json('choices.0.message.content');

        // Insertamos el summary dentro de la nota
        $note->content = trim(
            ($note->content ? $note->content . "\n\n" : '') .
            "## 📝 Resumen de la reunión\n\n" .
            $summary
        );

        $note->save();

        return back()->with('success', 'Resumen de Fathom importado correctamente');
    }

}
