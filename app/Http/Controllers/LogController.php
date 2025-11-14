<?php

namespace App\Http\Controllers;

use App\Models\LogEnvioMensajeria;
use App\Models\LogRespuestaCliente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LogController extends Controller
{
    /* ============================================================
     * 🔹 VISTA PRINCIPAL DE REPORTES (Mensajeria/Reporte.vue)
     * ============================================================ */
    public function index(Request $request)
    {
        $filtroCampania = $request->get('campania_id');
        $filtroEstado = $request->get('estado');

        // 📬 Logs de envíos
        $envios = LogEnvioMensajeria::with(['campania', 'destinatario'])
            ->when($filtroCampania, fn($q) => $q->where('campania_id', $filtroCampania))
            ->when($filtroEstado, fn($q) => $q->where('estado', $filtroEstado))
            ->latest()
            ->paginate(50)
            ->through(fn($envio) => [
                'id' => $envio->id,
                'campania' => $envio->campania?->nombre,
                'numero_cliente' => $envio->numero_cliente,
                'mensaje_enviado' => $envio->mensaje_enviado,
                'estado' => $envio->estado,
                'fecha_envio' => optional($envio->fecha_envio)->format('Y-m-d H:i:s'),
                'waha_sesion_nombre' => $envio->waha_sesion_nombre,
            ]);

        // 💬 Logs de respuestas
        $respuestas = LogRespuestaCliente::with('campania')
            ->latest()
            ->paginate(50)
            ->through(fn($r) => [
                'id' => $r->id,
                'campania' => $r->campania?->nombre,
                'numero_cliente' => $r->numero_cliente,
                'mensaje' => $r->mensaje,
                'formato_mensaje' => $r->formato_mensaje,
                'fecha_registro' => optional($r->fecha_registro)->format('Y-m-d H:i:s'),
            ]);

        return Inertia::render('Mensajeria/Reporte', [
            'envios' => $envios,
            'respuestas' => $respuestas,
            'filtros' => [
                'campania_id' => $filtroCampania,
                'estado' => $filtroEstado,
            ],
        ]);
    }

    /* ============================================================
     * 🔹 API: Envíos de una campaña (tiempo real)
     * ============================================================ */
    public function getEnviosByCampania($campaniaId)
    {
        $envios = LogEnvioMensajeria::where('campania_id', $campaniaId)
            ->latest()
            ->get(['id', 'numero_cliente', 'mensaje_enviado', 'estado', 'fecha_envio']);

        return response()->json($envios);
    }

    /* ============================================================
     * 🔹 API: Respuestas de clientes (tiempo real)
     * ============================================================ */
    public function getRespuestasByCampania($campaniaId)
    {
        $respuestas = LogRespuestaCliente::where('campania_id', $campaniaId)
            ->latest()
            ->get(['id', 'numero_cliente', 'mensaje', 'formato_mensaje', 'fecha_registro']);

        return response()->json($respuestas);
    }
}
