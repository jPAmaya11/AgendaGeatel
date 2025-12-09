<?php

namespace App\Http\Controllers;

use App\Models\Campania;
use App\Models\CampaniaMensaje;
use App\Models\CampaniaDestinatario;
use App\Models\CampaniaSesion;
use App\Models\WahaConexion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CampaniaController extends Controller
{
    /* ============================================================
     *  LISTAR CAMPAÑAS
     * ============================================================ */
    public function index()
    {
        $campanias = Campania::with(['mensajes'])
            ->orderByDesc('id')
            ->paginate(10);

        $campanias->getCollection()->transform(function ($c) {

            return [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'descripcion' => $c->descripcion,
                'fecha_inicio' => $c->fecha_inicio?->format('Y-m-d H:i'),
                'fecha_fin' => $c->fecha_fin?->format('Y-m-d H:i'),
                'estado' => $c->estado ?? 'pendiente',

                'total_destinatarios' => $c->total_destinatarios,

                'mensajes' => $c->mensajes->map(fn($m) => [
                    'orden' => $m->orden,
                    'mensaje' => $m->mensaje,
                    'tipo_mensaje' => $m->tipo_mensaje,
                    'url_archivo' => $m->url_archivo,
                ])->toArray(),

                'archivo_destinatarios' => $c->archivo_destinatarios,

                'usar_retraso_mensaje' => (bool)$c->usar_retraso_mensaje,
                'retraso_mensaje_min' => $c->retraso_mensaje_min,
                'retraso_mensaje_max' => $c->retraso_mensaje_max,
            ];
        });

        return response()->json([
            'ok' => true,
            'data' => $campanias->items(),
            'pagination' => [
                'total' => $campanias->total(),
                'current_page' => $campanias->currentPage(),
                'last_page' => $campanias->lastPage(),
                'per_page' => $campanias->perPage(),
                'from' => $campanias->firstItem(),
                'to' => $campanias->lastItem(),
            ]
        ]);
    }



    /* ============================================================
     *  CREAR CAMPAÑA
     * ============================================================ */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',

            // mensajes
            'mensajes' => 'required|array|min:1',
            'mensajes.*.mensaje' => 'required|string',
            'mensajes.*.tipo_mensaje' => 'required|string|in:texto,imagen,video,documento',
            'mensajes.*.url_archivo' => 'nullable|string',

            // destinatarios manuales
            'destinatarios_manual' => 'nullable|array',
            'destinatarios_manual.*.codigo_pais' => 'required|string',
            'destinatarios_manual.*.numero' => 'required|string',
            'destinatarios_manual.*.nombre' => 'nullable|string',

            // archivo
            'archivo_destinatarios' => 'nullable|file|mimes:xlsx,xls,csv|max:4096',

            // retraso
            'usar_retraso_mensaje' => 'boolean',
            'retraso_mensaje_min' => 'nullable|integer|min:1',
            'retraso_mensaje_max' => 'nullable|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $data) {

            $pathArchivo = null;
            $destinatarios = [];

            /* =======================================================
             * PROCESAR ARCHIVO EXCEL / CSV
             * ======================================================= */
            if ($request->hasFile('archivo_destinatarios')) {

    $file = $request->file('archivo_destinatarios');
    $pathArchivo = $file->store('campanias', 'public');

    $reader = IOFactory::createReaderForFile($file->getRealPath());
    $reader->setReadDataOnly(true);
    $excel = $reader->load($file->getRealPath());

    $rows = $excel->getActiveSheet()->toArray(null, true, true, true);

    foreach ($rows as $row) {
        $codigo = trim($row['A'] ?? '');
        $numero = trim($row['B'] ?? '');
        $nombre = trim($row['C'] ?? '');

        if (!$codigo || !$numero) continue;

        $codigo = preg_replace('/\D+/', '', $codigo);
        $numero = preg_replace('/\D+/', '', $numero);

        if (!$codigo || !$numero) continue;

        $destinatarios[] = [
            'codigo_pais' => $codigo,
            'numero' => $numero,
            'nombre' => $nombre,
        ];
    }
}


            /* =======================================================
             * DESTINATARIOS MANUALES
             * ======================================================= */
            if (!empty($data['destinatarios_manual'])) {

                foreach ($data['destinatarios_manual'] as $d) {

                    $codigo = preg_replace('/\D+/', '', $d['codigo_pais']);
                    $numero = preg_replace('/\D+/', '', $d['numero']);

                    if (!$codigo || !$numero) continue;

                    $destinatarios[] = [
                        'codigo_pais' => $codigo,
                        'numero' => $numero,
                        'nombre' => $d['nombre'] ?? null,
                    ];
                }
            }

            /* =======================================================
             * QUITAR DUPLICADOS
             * ======================================================= */
            $destinatarios = collect($destinatarios)
                ->unique(fn($d) => $d['codigo_pais'] . $d['numero'])
                ->values()
                ->all();

            /* =======================================================
             * CREAR CAMPAÑA
             * ======================================================= */
            $campania = Campania::create([
                'user_id' => Auth::id(),
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,

                'archivo_destinatarios' => $pathArchivo,

                'usar_retraso_mensaje' => $data['usar_retraso_mensaje'] ?? false,
                'retraso_mensaje_min' => $data['retraso_mensaje_min'] ?? null,
                'retraso_mensaje_max' => $data['retraso_mensaje_max'] ?? null,

                'estado' => 'pendiente',
            ]);

            /* =======================================================
             *  GUARDAR MENSAJES
             * ======================================================= */
            $orden = 1;

            foreach ($data['mensajes'] as $m) {
                CampaniaMensaje::create([
                    'campania_id' => $campania->id,
                    'orden' => $orden++,
                    'mensaje' => $m['mensaje'],
                    'tipo_mensaje' => $m['tipo_mensaje'],
                    'url_archivo' => $m['url_archivo'] ?? null,
                ]);
            }

            /* =======================================================
             *  GUARDAR DESTINATARIOS
             * ======================================================= */
            foreach ($destinatarios as $d) {
                CampaniaDestinatario::create([
                    'campania_id' => $campania->id,
                    'codigo_pais' => $d['codigo_pais'],
                    'numero' => $d['numero'],
                    'nombre' => $d['nombre'],
                ]);
            }

            $campania->update([
                'total_destinatarios' => count($destinatarios)
            ]);
        });

        return response()->json([
            'ok' => true,
            'message' => 'Campaña creada correctamente.',
        ]);
    }

    /* ============================================================
     *  INICIAR CAMPAÑA
     * ============================================================ */
    public function iniciar(Request $request, Campania $campania)
    {
        $data = $request->validate([
            'sesiones' => 'required|array|min:1', // cada sesión enviada como ['nombre' => 'WAHA1', 'numero_bot' => '1234']
        ]);

        DB::transaction(function () use ($campania, $data) {

            /*Guardar solo las sesiones seleccionadas para esta campaña */
            foreach ($data['sesiones'] as $sesion) {
                CampaniaSesion::create([
                    'campania_id' => $campania->id,
                    'nombre_sesion' => $sesion['nombre'],
                    'numero_bot' => $sesion['numero_bot'] ?? '-',
                    'estado' => 'inactiva', // en lugar de 'pendiente'
                    'total_enviados' => 0,
                    'total_errores' => 0,
                    'ultimo_ping' => now(),
                ]);
            }

            /*  Activar campaña */
            $campania->update([
                'estado' => 'activa',
                'fecha_inicio' => now(),
            ]);
        });

        return response()->json([
            'ok' => true,
            'message' => 'Sesiones asignadas, campaña iniciada.',
        ]);
    }


    /* ============================================================
     *  ELIMINAR CAMPAÑA
     * ============================================================ */
    public function destroy(Campania $campania)
    {
        $nombre = $campania->nombre;
        $campania->delete();

        return response()->json([
            'ok' => true,
            'message' => "Campaña «{$nombre}» eliminada correctamente.",
        ]);
    }

    public function procesarEnvio(Campania $campania)
    {
        $campania->load(['mensajes', 'destinatarios', 'sesiones']);

        $usarRetraso = (bool) $campania->usar_retraso_mensaje;
        $minRetraso = $campania->retraso_mensaje_min ?? 1;
        $maxRetraso = $campania->retraso_mensaje_max ?? 3;

        foreach ($campania->sesiones as $sesion) {
            // Buscar la conexión asignada a esta sesión
            $conexion = WahaConexion::all()->first(function ($c) use ($sesion) {
                try {
                    $sesiones = Http::withOptions(['verify' => false])
                        ->withHeaders(['x-api-key' => trim($c->token_api)])
                        ->timeout(10)
                        ->get(rtrim($c->host, '/') . '/api/sessions')
                        ->json();

                    return collect($sesiones)->pluck('name')->contains($sesion->nombre_sesion);
                } catch (\Throwable $e) {
                    Log::warning("Error obteniendo sesiones de {$c->nombre}: " . $e->getMessage());
                    return false;
                }
            });

            if (!$conexion) {
                Log::error("Sesión {$sesion->nombre_sesion} no encontrada en ninguna conexión");
                continue;
            }

            foreach ($campania->destinatarios as $destinatario) {
                foreach ($campania->mensajes as $mensaje) {

                    $intentos = 0;
                    $maxIntentos = 3; // reintentos por mensaje

                    do {
                        $intentos++;
                        try {
                            $payload = [
                                'chatId' => $destinatario->codigo_pais . $destinatario->numero . '@c.us',
                                'text' => $mensaje->mensaje,
                                'session' => $sesion->nombre_sesion
                            ];

                            $response = Http::withOptions(['verify' => false])
                                ->withHeaders(['x-api-key' => trim($conexion->token_api)])
                                ->timeout(10)
                                ->post(rtrim($conexion->host, '/') . '/api/sendText', $payload);

                            if ($response->failed()) {
                                throw new \Exception($response->body());
                            }

                            $sesion->increment('total_enviados');

                            // Aplicar retraso si está activado
                            if ($usarRetraso) {
                                $sleepTime = rand($minRetraso, $maxRetraso);
                                sleep($sleepTime);
                            }

                            break; // mensaje enviado correctamente

                        } catch (\Exception $e) {
                            $sesion->increment('total_errores');
                            Log::error("Error enviando a {$destinatario->numero} en sesión {$sesion->nombre_sesion} (intento $intentos): {$e->getMessage()}");

                            // Si falla, esperar 2 segundos antes de reintentar
                            sleep(2);

                            if ($intentos >= $maxIntentos) {
                                Log::error("No se pudo enviar el mensaje a {$destinatario->numero} tras {$maxIntentos} intentos");
                                break;
                            }
                        }
                    } while ($intentos < $maxIntentos);
                }
            }

            $sesion->update(['ultimo_ping' => now()]);
        }

        $campania->update(['estado' => 'finalizada', 'fecha_fin' => now()]);

        return response()->json([
            'ok' => true,
            'message' => 'Campaña procesada y finalizada correctamente.'
        ]);
    }
}
