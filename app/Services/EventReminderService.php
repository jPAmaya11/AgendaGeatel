<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Mail;
use App\Mail\EventReminderMail;
use Illuminate\Support\Facades\Http;


class EventReminderService
{
    protected ?string $wahaApiUrl;
    protected ?string $wahaApiKey;
    protected ?string $whatsappFrom;

    public function __construct()
    {        
        $this->wahaApiUrl = env('WAHA_API_URL');
        $this->wahaApiKey = env('WAHA_API_KEY');
        $this->whatsappFrom = env('WAHA_WHATSAPP_FROM');

        Log::info('WAHA API URL: ' . env('WAHA_API_URL'));
        Log::info('WAHA API Key: ' . env('WAHA_API_KEY'));
        Log::info('WAHA WhatsApp From: ' . env('WAHA_WHATSAPP_FROM'));
    }

    /**
     * Punto de entrada: decide si se debe enviar el recordatorio
     * según reminder_type y el canal configurado en el EVENTO.
     */
    public function sendReminder(Event $event): bool
    {
        // 🔹 Si no hay canal, marcamos como failed UNA sola vez y salimos
        if (empty($event->reminder_channel)) {
            Log::warning('Evento sin canal de recordatorio configurado.', [
                'event_id' => $event->id,
            ]);

            $event->reminder_status = 'failed';
            $event->save();

            return false;
        }

        // Tipos válidos
        $validReminderTypes = ['exact', 'before', 'recurrent'];

        if (!in_array($event->reminder_type, $validReminderTypes)) {
            Log::warning('Tipo de recordatorio no soportado.', [
                'event_id'      => $event->id,
                'reminder_type' => $event->reminder_type,
            ]);

            $event->reminder_status = 'failed';
            $event->save();

            return false;
        }

        $user = $event->user;
        if (!$user) {
            Log::warning('Evento sin usuario asociado.', [
                'event_id' => $event->id,
            ]);
            $event->reminder_status = 'failed';
            $event->save();
            return false;
        }

        if (empty($user->phone) && empty($user->email)) {
            Log::warning('Usuario sin datos de contacto (teléfono/correo).', [
                'event_id' => $event->id,
                'user_id'  => $user->id ?? null,
            ]);
            $event->reminder_status = 'failed';
            $event->save();
            return false;
        }

        // Mensaje base
        $message = sprintf(
            "Recordatorio: %s\nFecha: %s\nLugar: %s",
            $event->title,
            Carbon::parse($event->start_at)->format('d/m/Y H:i'),
            $event->location ?? 'Sin ubicación'
        );

        // 🔹 CASO ESPECIAL: RECURRENT
        if ($event->reminder_type === 'recurrent') {
            // 👉 Toda la lógica de recurrente se maneja aquí dentro
            return $this->handleRecurrentReminder($event, $message);
        }

        // 🔹 CASOS NORMALITOS: EXACT Y BEFORE
        $ok = false;

        try {
            switch ($event->reminder_type) {
                case 'exact':
                    // Recordatorio exacto: ya viene reminder_at calculado
                    $ok = $this->sendReminderByChannel($event, $message, $event->reminder_at);
                    break;

                case 'before':
                    // Recordatorio antes del evento (por seguridad recalculamos aquí)
                    $reminderAt = Carbon::parse($event->start_at);
                    if ($event->reminder_unit === 'hours') {
                        $reminderAt->subHours((int) $event->reminder_interval);
                    } else {
                        $reminderAt->subMinutes((int) $event->reminder_interval);
                    }
                    $ok = $this->sendReminderByChannel($event, $message, $reminderAt);
                    break;
            }
        } catch (\Throwable $e) {
            Log::error('Error al enviar recordatorio de evento.', [
                'event_id' => $event->id,
                'error'    => $e->getMessage(),
            ]);
            $ok = false;
        }

        // Enviar recordatorio por correo si está configurado
/*        if ($user->email && $event->reminder_channel === 'email') {
            try {
                Mail::to($user->email)->send(new EventReminderMail($event, $message));
                Log::info("Correo de recordatorio enviado para el evento ID {$event->id}");
            } catch (\Throwable $e) {
                Log::error("Error al enviar correo de recordatorio para evento ID {$event->id}: " . $e->getMessage());
                $ok = false;
            }
        }*/

        // 💾 Actualizar estado para exact/before
        if ($ok) {
            $event->reminder_status  = 'sent';
            $event->reminder_sent_at = now();
        } else {
            $event->reminder_status  = 'failed';
        }

        $event->save();

        return $ok;
    }

    /**
     * Manejo de recordatorios recurrentes:
     * - Envía en reminder_at si ya llegó la hora
     * - Luego suma el intervalo (minutos/horas) y actualiza reminder_at
     * - Repite mientras la fecha del siguiente recordatorio sea ANTES de start_at
     * - Cuando now >= start_at, se detiene y marca como 'sent'
     */
    protected function handleRecurrentReminder(Event $event, string $message): bool
    {
        $now   = Carbon::now();
        $start = Carbon::parse($event->start_at);

        // Si ya empezó la reunión, no mandamos más recordatorios
        if ($now->greaterThanOrEqualTo($start)) {
            // Si ya llegó la hora de la reunión, enviamos el último recordatorio si corresponde
            if ($event->reminder_status !== 'sent') {
                // Enviar el último recordatorio si no se ha enviado
                $this->sendReminderByChannel($event, $message, $start);
                $event->reminder_status  = 'sent';
                $event->reminder_sent_at = $now;
                $event->save();
            }
            return false;  // Ya no enviamos más recordatorios
        }

        // Si aún no es la hora del evento, procedemos con los recordatorios recurrentes
        if (empty($event->reminder_at)) {
            Log::warning('Recurrente sin reminder_at inicial.', [
                'event_id' => $event->id,
            ]);
            $event->reminder_status = 'failed';
            $event->save();
            return false;
        }

        $nextReminder = Carbon::parse($event->reminder_at);

        // Si todavía NO ha llegado la hora del siguiente recordatorio, no hacemos nada
        if ($nextReminder->greaterThan($now)) {
            return false;
        }

        // Ya toca enviar el recordatorio
        $sent = $this->sendReminderByChannel($event, $message, $nextReminder);

        if (!$sent) {
            // Si falló, dejamos reminder_status como 'pending' para reintentar en el próximo ciclo
            Log::warning('Fallo envío de recordatorio recurrente, se intentará de nuevo.', [
                'event_id' => $event->id,
            ]);
            return false;
        }

        // Ajuste importante: Verificamos si el siguiente reminder es el último antes del evento
        if ($nextReminder->equalTo($start)) {
            // Si ya es la hora exacta del evento, marcamos como 'sent'
            $event->reminder_status  = 'sent';
            $event->reminder_sent_at = $now;
            $event->save();
            return true;
        }

        // Si aún no llega la hora, programamos el siguiente recordatorio
        $interval = (int) ($event->reminder_interval ?? 0);
        if ($interval <= 0) {
            Log::warning('Intervalo inválido en recordatorio recurrente.', [
                'event_id' => $event->id,
                'interval' => $event->reminder_interval,
            ]);
            $event->reminder_status = 'failed';
            $event->save();
            return false;
        }

        // Ajustar reminder_at sumando el intervalo (por ejemplo, 10 minutos)
        if ($event->reminder_unit === 'hours') {
            $nextReminder->addHours($interval);
        } else {
            $nextReminder->addMinutes($interval);
        }

        // Si el próximo reminder ya sería en o después del inicio del evento → cerramos ciclo
        if ($nextReminder->greaterThanOrEqualTo($start)) {
            $event->reminder_status  = 'sent';
            $event->reminder_sent_at = $now;
        } else {
            // Todavía falta para el evento → programamos el siguiente reminder
            $event->reminder_at      = $nextReminder;
            // MUY IMPORTANTE: dejamos reminder_status como 'pending'
            $event->reminder_status  = 'pending';
        }

        $event->save();

        return true;
    }

    /**
     * Envía el recordatorio si ya llegó la hora programada.
     * Usa el canal configurado en el EVENTO (whatsapp/email/both).
     */
    protected function sendReminderByChannel(Event $event, string $message, $reminderAt): bool
    {
        // No enviar si aún no llega la hora exacta
        if ($reminderAt instanceof Carbon) {
            if ($reminderAt->greaterThan(Carbon::now())) {
                return false;
            }
        } else {
            $reminderAt = Carbon::parse($reminderAt);
            if ($reminderAt->greaterThan(Carbon::now())) {
                return false;
            }
        }

        $user    = $event->user;
        $channel = $event->reminder_channel;
        $ok      = false;

        switch ($channel) {
            case 'whatsapp':
                $ok = $user->phone
                    ? $this->sendWhatsapp($user->phone, $message)
                    : false;
                break;

            case 'email':
                $ok = $user->email
                    ? $this->sendEmail($user->email, $message)
                    : false;
                break;

            case 'both':
                $waOk   = $user->phone ? $this->sendWhatsapp($user->phone, $message) : false;
                $mailOk = $user->email ? $this->sendEmail($user->email, $message)    : false;
                $ok = $waOk && $mailOk;
                break;

            default:
                Log::warning('Canal de recordatorio no soportado.', [
                    'event_id' => $event->id,
                    'channel'  => $channel,
                ]);
                return false;
        }

        return $ok;
    }

    protected function sendWhatsapp(string $to, string $message): bool
    {
        if (!$this->wahaApiKey || !$this->whatsappFrom) {
            Log::error('WAHA API key or WhatsApp number not configured.');
            return false;
        }

        // formato correcto
        $to = preg_replace('/\D+/', '', $to);

        if (!str_ends_with($to, '@c.us')) {
            $to .= '@c.us';
        }

        $session = 'NOTIFICACIONES_GEATEL_PERU';        

        try {
            $response = Http::withOptions([
                'verify' => false, 
            ])
            ->withHeaders([
                'x-api-key' => $this->wahaApiKey,
            ])
            ->post($this->wahaApiUrl . '/api/sendText', [
                'chatId' => $to,
                'reply_to' => null,
                'text' => $message,
                'linkPreview' => true,
                'linkPreviewHighQuality' => false,
                'session' => $session,
            ]);

            // Verificación de la respuesta
            if ($response->successful()) {
                Log::info('WhatsApp enviado correctamente', ['to' => $to]);
                return true;
            } else {
                Log::error('Error al enviar WhatsApp a través de Waha', [
                    'response' => $response->body(),
                    'status_code' => $response->status(),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error al realizar la solicitud a WAHA', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }



    /**
     * Envío simple por correo usando Gmail (ya que ahora es WhatsApp + email).
     * Deberías tener configurado MAIL_MAILER, MAIL_HOST, etc. en .env.
     */
    protected function sendEmail(string $to, string $message): bool
    {
        try {
            Mail::raw($message, function ($mail) use ($to) {
                $mail->to($to)
                     ->subject('Recordatorio de evento');
            });

            Log::info('Email de recordatorio enviado correctamente', ['to' => $to]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Error enviando email de recordatorio', [
                'to'    => $to,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
