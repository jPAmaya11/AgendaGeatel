<?php

namespace App\Http\Controllers;

use App\Models\WahaConexion;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WahaConexionController extends Controller
{
    /* ============================================================
     * 🔹 VISTA PRINCIPAL (INERTIA)
     * ============================================================ */
    public function index()
    {
        return Inertia::render('Mensajeria/Conexion', [
            'conexiones' => WahaConexion::with('admin:id,name')->orderByDesc('id')->paginate(10),
            'admins' => User::select('id', 'name')->get(),
            'success' => session('success'),
        ]);
    }

    /* ============================================================
     * 🔹 LISTAR CONEXIONES (JSON)
     * ============================================================ */
    public function list()
    {
        $conexiones = WahaConexion::with('admin:id,name')
           
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $conexiones]);
    }


    /* ============================================================
     * 🔹 CREAR CONEXIÓN
     * ============================================================ */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150', Rule::unique('waha_conexiones', 'nombre')],
            'host' => 'required|url',
            'token_api' => 'required|string|max:255',
            'user_id_admin' => 'nullable|exists:users,id',
        ]);

        $conexion = WahaConexion::create(array_merge($data, ['estado' => 'inactivo']));

        return response()->json([
            'ok' => true,
            'message' => "Conexión WAHA «{$conexion->nombre}» creada correctamente.",
            'data' => $conexion
        ]);
    }

    /* ============================================================
     * 🔹 ACTUALIZAR CONEXIÓN
     * ============================================================ */
    public function update(Request $request, WahaConexion $waha)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150', Rule::unique('waha_conexiones')->ignore($waha->id)],
            'host' => 'required|url',
            'token_api' => 'required|string|max:255',
            'user_id_admin' => 'nullable|exists:users,id',
            'estado' => 'nullable|string'
        ]);

        $waha->update($data);

        return response()->json(['ok' => true, 'message' => 'Conexión actualizada correctamente.']);
    }

    /* ============================================================
     * 🔹 ELIMINAR CONEXIÓN
     * ============================================================ */
    public function destroy(WahaConexion $waha)
    {
        $nombre = $waha->nombre;
        $waha->delete();

        return response()->json(['ok' => true, 'message' => "Conexión «{$nombre}» eliminada correctamente."]);
    }

    /* ============================================================
     * 🔹 PROBAR CONEXIÓN / OBTENER SESIONES
     * ============================================================ */
    public function test($id)
    {
        $conexion = WahaConexion::find($id);
        if (!$conexion) {
            return response()->json(['ok' => false, 'message' => "Conexión no encontrada"], 404);
        }

        return $this->fetchSessions($conexion, true);
    }

    /* ============================================================
     * 🔹 SESIONES EN TIEMPO REAL
     * ============================================================ */
    public function sesionesRealTime($id)
    {
        $conexion = WahaConexion::find($id);
        if (!$conexion) {
            return response()->json(['ok' => false, 'message' => "Conexión no encontrada"], 404);
        }

        return $this->fetchSessions($conexion);
    }

    /* ============================================================
     * 🔹 FUNCIÓN INTERNA: CONSULTAR SESIONES WAHA
     * ============================================================ */
    private function fetchSessions(WahaConexion $waha, bool $normalize = true)
    {
        try {
            $url = rtrim($waha->host, '/') . '/api/sessions';

            $response = Http::withOptions(['verify' => false])
                ->timeout(10)
                ->withHeaders(['x-api-key' => trim($waha->token_api)])
                ->get($url);

            if ($response->failed()) {
                $waha->update(['estado' => 'error']);
                return response()->json(['ok' => false, 'message' => 'Error del servidor WAHA']);
            }

            $waha->update(['estado' => 'activo']);
            $data = $response->json();

            if ($normalize) {
                $data = collect($data)->map(fn($s) => [
                    'nombre' => $s['name'] ?? 'Sin nombre',
                    'telefono' => isset($s['me']['id'])
                        ? explode(':', $s['me']['id'])[0]
                        : '-',
                    'estado' => strtolower($s['status'] ?? 'desconectado'),
                    'enviados' => 0,
                    'pendientes' => 0,
                    'pushName' => $s['me']['pushName'] ?? '-',
                ]);
            }

            return response()->json([
                'ok' => true,
                'message' => 'Sesiones obtenidas correctamente.',
                'data' => $data,
                'estado' => 'activo'
            ]);
        } catch (\Throwable $e) {
            $waha->update(['estado' => 'error']);
            Log::error("WAHA {$waha->nombre} error: {$e->getMessage()}");
            return response()->json(['ok' => false, 'message' => 'No se pudo conectar a WAHA.'], 500);
        }
    }

    /* ============================================================
     * 🔹 SESIONES DISPONIBLES PARA CAMPAÑAS
     * ============================================================ */
    public function sesionesDisponibles()
    {
        $todas = collect();

        foreach (WahaConexion::all() as $conexion) {
            try {
                $url = rtrim($conexion->host, '/') . '/api/sessions';
                $res = Http::withOptions(['verify' => false])
                    ->withHeaders(['x-api-key' => trim($conexion->token_api)])
                    ->timeout(10)
                    ->get($url);

                if ($res->successful()) {
                    $sesiones = collect($res->json())->map(fn($s) => [
                        'nombre' => $s['name'] ?? 'Sin nombre',
                        'telefono' => isset($s['me']['id'])
                            ? explode(':', $s['me']['id'])[0]
                            : '-',
                        'estado' => strtolower($s['status'] ?? 'desconectado'),
                        'conexion_nombre' => $conexion->nombre,
                        'conexion_id' => $conexion->id,
                        'host' => $conexion->host,
                        'token_api' => $conexion->token_api,
                        'pushName' => $s['me']['pushName'] ?? '-',
                    ]);

                    $todas = $todas->merge($sesiones);
                }
            } catch (\Throwable $e) {
                Log::warning("Error al obtener sesiones de {$conexion->nombre}: " . $e->getMessage());
            }
        }

        return response()->json(['ok' => true, 'data' => $todas->values()]);
    }
    /* ============================================================
     * 🔹 ENVIAR MENSAJE (PRUEBA O PRODUCCIÓN)
     * ============================================================ */
    public function enviarMensaje(Request $request)
    {
        $data = $request->validate([
            'conexion_id' => 'required|exists:waha_conexiones,id',
            'numero' => 'required|string',
            'mensaje' => 'required|string',
            'nombre_sesion' => 'required|string',
        ]);

        try {
            $conexion = WahaConexion::findOrFail($data['conexion_id']);

            $url = rtrim($conexion->host, '/') . '/api/sendText';

            $payload = [
                'chatId' => $data['numero'] . '@c.us',   // Número destino
                'text' => $data['mensaje'],
                'session' => $data['nombre_sesion'],     // 👈 Nombre de la sesión WAHA
            ];

            $response = Http::withOptions(['verify' => false])
                ->withHeaders(['x-api-key' => trim($conexion->token_api)])
                ->post($url, $payload);

            if ($response->failed()) {
                Log::error("WAHA error ({$conexion->nombre}): " . $response->body());
                return response()->json([
                    'ok' => false,
                    'message' => ' Error al enviar el mensaje.',
                    'error' => $response->body(),
                ], $response->status());
            }

            return response()->json([
                'ok' => true,
                'message' => ' Mensaje enviado correctamente.',
                'data' => $response->json(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Error interno al enviar mensaje WAHA: " . $e->getMessage());
            return response()->json([
                'ok' => false,
                'message' => 'Error interno del servidor.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
