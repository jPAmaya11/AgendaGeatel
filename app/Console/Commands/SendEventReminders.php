<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Services\EventReminderService;
use Carbon\Carbon;

class SendEventReminders extends Command
{
    protected $signature = 'events:send-reminders';

    protected $description = 'Enviar recordatorios de eventos pendientes según su fecha de reminder_at.';

    public function handle(EventReminderService $reminderService): int
    {
        $now         = Carbon::now();
        $windowStart = (clone $now)->subMinute();

        $this->info(
            'Buscando eventos con recordatorios pendientes entre: ' .
            $windowStart->toDateTimeString() . ' y ' . $now->toDateTimeString()
        );

        $events = Event::whereNotNull('reminder_at')
            ->whereBetween('reminder_at', [$windowStart, $now])
            ->where('reminder_status', 'pending')
            ->where('status', '!=', 'cancelled')
            ->get();

        if ($events->isEmpty()) {
            $this->info('No hay recordatorios pendientes por enviar en esta ventana.');
            return Command::SUCCESS;
        }

        $this->info('Eventos encontrados: ' . $events->count());

        foreach ($events as $event) {
            $this->info("Enviando recordatorio para evento ID {$event->id} ({$event->title})");

            $ok = $reminderService->sendReminder($event);

            if ($ok) {
                $this->info("✔ Recordatorio enviado correctamente para evento ID {$event->id}");
            } else {
                $this->error("✖ Falló el envío del recordatorio para evento ID {$event->id}");
            }
        }

        return Command::SUCCESS;
    }
}
