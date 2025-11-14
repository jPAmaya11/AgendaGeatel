<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = ['current_password', 'password', 'password_confirmation'];

    public function register(): void
    {
        //
    }

    public function render($request, Throwable $exception)
    {
        Log::debug('🔎 Excepción lanzada: ' . get_class($exception));
        if ($exception instanceof AuthenticationException) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->guest(route('login'));
        }

        // Si está autenticado pero no tiene permisos
        if ($exception instanceof AuthorizationException ||
            ($exception instanceof HttpException && $exception->getStatusCode() === 403)) {

            // Si es petición Inertia (frontend Inertia.js)
            if ($request->header('X-Inertia')) {
                return inertia()->location(route('dashboard'));
            }

            // Si es petición JSON pura (API)
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No autorizado.'], 403);
            }

            // Si es petición tradicional (formulario, navegador directo)
            return redirect()->route('dashboard')->with('error', 'No tienes permiso para acceder a esta sección.');
        }

        return parent::render($request, $exception);
    }
}