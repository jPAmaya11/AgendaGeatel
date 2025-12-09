<?php

namespace App\Http\Controllers;

use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GoogleCalendarController extends Controller
{
    public function redirect()
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setScopes([
            GoogleCalendar::CALENDAR,
        ]);

        $authUrl = $client->createAuthUrl();

        return redirect()->away($authUrl);
    }

    public function callback(Request $request)
    {
        $code = $request->get('code');

        if (!$code) {
            return redirect()->route('events.index')
                ->with('error', 'No se recibió el código de autorización de Google.');
        }

        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));

        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            Log::error('Error al obtener token de Google', ['error' => $token['error']]);
            return redirect()->route('events.index')
                ->with('error', 'Error al conectar con Google Calendar.');
        }

        $user = Auth::user();
        $user->google_access_token = json_encode($token);
        $user->google_refresh_token = $token['refresh_token'] ?? $user->google_refresh_token;
        $user->google_token_expires_at = now()->addSeconds($token['expires_in'] ?? 3600);
        $user->save();

        return redirect()->route('events.index')
            ->with('success', 'Google Calendar conectado correctamente.');
    }
}
