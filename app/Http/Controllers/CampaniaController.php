<?php

namespace App\Http\Controllers;

use App\Models\Campania;
use App\Models\CampaniaMensaje;
use App\Models\CampaniaDestinatario;
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
                    'mensaje' => $m->mensaje,
                    'tipo_mensaje' => $m->tipo_mensaje,
                    'url_archivo' => $m->url_archivo,
                ]),
                'archivo_destinatarios' => $c->archivo_destinatarios,
                'waha_sesiones' => $c->waha_sesiones ?? [],
                'usar_retraso_lote' => $c->usar_retraso_lote,
                'retraso_lote_min' => $c->retraso_lote_min,
                'retraso_lote_max' => $c->retraso_lote_max,
                'retraso_lote_cada' => $c->retraso_lote_cada,
                'usar_retraso_mensaje' => $c->usar_retraso_mensaje,
                'retraso_mensaje_min' => $c->retraso_mensaje_min,
                'retraso_mensaje_max' => $c->retraso_mensaje_max,
            ];
        });

        return response()->json($campanias);
    }

    /* ============================================================
     *  CREAR CAMPAÑA
     * ============================================================ */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'mensajes' => 'required|array|min:1',
            'mensajes.*.mensaje' => 'required|string',
            'mensajes.*.tipo_mensaje' => 'required|string|in:texto,imagen,video,documento',
            'mensajes.*.url_archivo' => 'nullable|string',
            'archivo_destinatarios' => 'nullable|file|mimes:xlsx,xls,csv|max:2048',
            'numeros_manual' => 'nullable|array',
            'numeros_manual.*' => 'string',

            'usar_retraso_lote' => 'boolean',
            'retraso_lote_min' => 'nullable|integer|min:1',
            'retraso_lote_max' => 'nullable|integer|min:1',
            'retraso_lote_cada' => 'nullable|integer|min:1',
            'usar_retraso_mensaje' => 'boolean',
            'retraso_mensaje_min' => 'nullable|integer|min:1',
            'retraso_mensaje_max' => 'nullable|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $data) {
            $pathArchivo = null;
            $numeros = [];

            // Leer archivo Excel o CSV
            if ($request->hasFile('archivo_destinatarios')) {
                $file = $request->file('archivo_destinatarios');
                $pathArchivo = $file->store('campanias', 'public');

                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                foreach ($rows as $row) {
                    foreach ($row as $col) {
                        $col = trim(preg_replace('/[\s\-\(\)]/', '', $col));
                        if (preg_match('/^\+?\d{6,15}$/', $col)) {
                            $numeros[] = $col;
                            break;
                        }
                    }
                }
            }

            // Agregar números manuales
            if (!empty($data['numeros_manual'])) {
                foreach ($data['numeros_manual'] as $n) {
                    $n = trim($n);
                    if ($n) $numeros[] = $n;
                }
            }

            $numeros = array_unique($numeros);

            // Crear campaña
            $campania = Campania::create([
                'user_id' => Auth::id(),
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'fecha_inicio' => $data['fecha_inicio'] ?? null,
                'fecha_fin' => $data['fecha_fin'] ?? null,
                'estado' => 'pendiente',
                'archivo_destinatarios' => $pathArchivo,

                'usar_retraso_lote' => $data['usar_retraso_lote'] ?? false,
                'retraso_lote_min' => $data['retraso_lote_min'] ?? null,
                'retraso_lote_max' => $data['retraso_lote_max'] ?? null,
                'retraso_lote_cada' => $data['retraso_lote_cada'] ?? null,
                'usar_retraso_mensaje' => $data['usar_retraso_mensaje'] ?? false,
                'retraso_mensaje_min' => $data['retraso_mensaje_min'] ?? null,
                'retraso_mensaje_max' => $data['retraso_mensaje_max'] ?? null,
            ]);

            // Crear mensajes
            foreach ($data['mensajes'] as $m) {
                CampaniaMensaje::create([
                    'campania_id' => $campania->id,
                    'mensaje' => $m['mensaje'],
                    'tipo_mensaje' => $m['tipo_mensaje'],
                    'url_archivo' => $m['url_archivo'] ?? null,
                ]);
            }

            // Guardar destinatarios
            foreach ($numeros as $numero) {
                CampaniaDestinatario::create([
                    'campania_id' => $campania->id,
                    'numero' => $numero,
                ]);
            }

            $campania->update(['total_destinatarios' => count($numeros)]);
        });

        return response()->json(['ok' => true, 'message' => 'Campaña creada correctamente.']);
    }

    /* =================    ===========================================
     *  INICIAR CAMPAÑA (ENVÍO REAL SI HAY URL/TOKEN)
     * ============================================================ */
    public function iniciar(Request $request, Campania $campania)
    {
        $data = $request->validate([
            'sesiones' => 'required|array|min:1',
        ]);

        // Actualizar campaña como activa
        $campania->update([
            'waha_sesiones' => $data['sesiones'],
            'estado' => 'activa',
            'fecha_inicio' => now(),
        ]);

        $mensajes = CampaniaMensaje::where('campania_id', $campania->id)->get();
        $destinatarios = CampaniaDestinatario::where('campania_id', $campania->id)->get();
        $conexiones = WahaConexion::all();

        $errores = [];
        $usarRetraso = $campania->usar_retraso_mensaje;
        $min = $campania->retraso_mensaje_min ?? 0;
        $max = $campania->retraso_mensaje_max ?? 0;

        foreach ($data['sesiones'] as $nombreSesion) {
            // Buscar conexión válida con URL y token
            $conexion = $conexiones->first(fn($c) => $c->host && $c->token_api);
            if (!$conexion) {
                $errores[] = ['sesion' => $nombreSesion, 'error' => 'No hay conexión WAHA configurada con host y token.'];
                continue;
            }

            foreach ($destinatarios as $dest) {
                try {
                    // Elegir un mensaje aleatorio para este destinatario
                    $m = $mensajes->random();

                    $numero = preg_replace('/\D+/', '', $dest->numero);
                    $payload = [
                        'chatId' => $numero . '@c.us',
                        'text' => $m->mensaje,
                        'session' => $nombreSesion,
                    ];

                    $res = Http::withOptions(['verify' => false])
                        ->withHeaders(['x-api-key' => trim($conexion->token_api)])
                        ->post(rtrim($conexion->host, '/') . '/api/sendText', $payload);

                    if ($res->failed()) {
                        $errores[] = [
                            'numero' => $dest->numero,
                            'mensaje' => $m->mensaje,
                            'error' => $res->body()
                        ];
                        Log::error("Error enviando a {$dest->numero}: " . $res->body());
                    }

                    if ($usarRetraso && $min > 0 && $max >= $min) {
                        sleep(rand($min, $max));
                    }

                } catch (\Throwable $e) {
                    $errores[] = [
                        'numero' => $dest->numero,
                        'mensaje' => $m->mensaje,
                        'error' => $e->getMessage()
                    ];
                    Log::error("Excepción enviando a {$dest->numero}: " . $e->getMessage());
                }
            }
        }

        $campania->update(['estado' => 'finalizada', 'fecha_fin' => now()]);

        return response()->json([
            'ok' => true,
            'message' => 'Campaña iniciada correctamente (solo si hay host y token).',
            'errores' => $errores,
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
}
