<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    protected function makeClientForUser(User $user): ?GoogleClient
    {
        if (!$user->google_access_token) {
            return null; // Usuario no conectado a Google
        }

        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setScopes([
            GoogleCalendar::CALENDAR,
        ]);

        $accessToken = json_decode($user->google_access_token, true);
        $client->setAccessToken($accessToken);

        // Refrescar token si está vencido
        if ($client->isAccessTokenExpired()) {
            if (!$user->google_refresh_token) {
                Log::warning('Usuario sin refresh token de Google', ['user_id' => $user->id]);
                return null;
            }

            $client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
            $newToken = $client->getAccessToken();

            $user->google_access_token = json_encode($newToken);
            $user->google_token_expires_at = now()->addSeconds($newToken['expires_in'] ?? 3600);
            $user->save();
        }

        return $client;
    }

    public function createEvent(Event $event): ?string
    {
        $user = $event->user;
        if (!$user) {
            Log::error('Evento sin usuario para Google Calendar', ['event_id' => $event->id]);
            return null;
        }

        $client = $this->makeClientForUser($user);
        if (!$client) {
            return null;
        }

        $service = new GoogleCalendar($client);
        $calendarId = 'primary'; // Calendario principal del usuario
        $timezone = config('app.timezone', 'America/Lima');

        $googleEvent = new GoogleCalendar\Event([
            'summary'     => $event->title,
            'description' => $event->description ?? '',
            'location'    => $event->location ?? '',
            'start'       => [
                'dateTime' => $event->start_at?->toAtomString(),
                'timeZone' => $timezone,
            ],
            'end'         => [
                'dateTime' => $event->end_at?->toAtomString() ?? $event->start_at?->copy()->addHour()->toAtomString(),
                'timeZone' => $timezone,
            ],
        ]);

        $created = $service->events->insert($calendarId, $googleEvent);

        return $created->id ?? null;
    }

    public function updateEvent(Event $event): bool
    {
        $user = $event->user;
        if (!$user || !$event->google_event_id) {
            return false;
        }

        $client = $this->makeClientForUser($user);
        if (!$client) {
            return false;
        }

        $service = new GoogleCalendar($client);
        $calendarId = 'primary';
        $timezone = config('app.timezone', 'America/Lima');

        try {
            $googleEvent = $service->events->get($calendarId, $event->google_event_id);

            $googleEvent->setSummary($event->title);
            $googleEvent->setDescription($event->description ?? '');
            $googleEvent->setLocation($event->location ?? '');

            $start = $googleEvent->getStart();
            $end   = $googleEvent->getEnd();

            $start->setDateTime($event->start_at?->toAtomString());
            $start->setTimeZone($timezone);

            $end->setDateTime($event->end_at?->toAtomString() ?? $event->start_at?->copy()->addHour()->toAtomString());
            $end->setTimeZone($timezone);

            $googleEvent->setStart($start);
            $googleEvent->setEnd($end);

            $service->events->update($calendarId, $googleEvent->getId(), $googleEvent);

            return true;
        } catch (\Throwable $e) {
            Log::error('Error al actualizar evento en Google Calendar', [
                'event_id' => $event->id,
                'error'    => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function deleteEvent(Event $event): bool
    {
        $user = $event->user;
        if (!$user || !$event->google_event_id) {
            return false;
        }

        $client = $this->makeClientForUser($user);
        if (!$client) {
            return false;
        }

        $service = new GoogleCalendar($client);
        $calendarId = 'primary';

        try {
            $service->events->delete($calendarId, $event->google_event_id);
            return true;
        } catch (\Throwable $e) {
            Log::error('Error al eliminar evento en Google Calendar', [
                'event_id' => $event->id,
                'error'    => $e->getMessage(),
            ]);
            return false;
        }
    }
}
