<?php

namespace App\Http\Controllers;

use App\Models\WahaConexion;
use App\Models\ConexionUsuarioFiltro;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WahaConexionController extends Controller
{
    /* ============================================================
     * VISTA PRINCIPAL
     * ============================================================ */
    public function index()
    {
        return Inertia::render('Mensajeria/Conexion', [
            'conexiones' => WahaConexion::with('admin:id,name')
                ->orderByDesc('id')
                ->paginate(10),
            'admins' => User::select('id', 'name')->get(),
            'success' => session('success'),
        ]);
    }

    /* ============================================================
     * LISTAR CONEXIONES (JSON)
     * ============================================================ */
    public function list()
    {
        return response()->json([
            'data' => WahaConexion::with('admin:id,name')->orderByDesc('id')->get()
        ]);
    }

    /* ============================================================
     * CREAR CONEXIÓN
     * ============================================================ */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:150',Rule::unique('waha_conexiones')],
            'host' => 'required|url',
            'token_api' => 'required|string|max:255',
            'user_id_admin' => 'nullable|exists:users,id',
        ]);

        $conexion = WahaConexion::create([
            ...$data,
            'estado' => 'inactivo'
        ]);

        return response()->json([
            'ok' => true,
            'message' => "Conexión «{$conexion->nombre}» creada correctamente.",
            'data' => $conexion
        ]);
    }

    /* ============================================================
     * ACTUALIZAR CONEXIÓN
     * ============================================================ */
    public function update(Request $request, WahaConexion $waha)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:150',Rule::unique('waha_conexiones')->ignore($waha->id)],
            'host' => 'required|url',
            'token_api' => 'required|string|max:255',
            'user_id_admin' => 'nullable|exists:users,id',
            'estado' => 'nullable|string'
        ]);

        $waha->update($data);

        return response()->json(['ok' => true,'message' => 'Conexión actualizada correctamente.']);
    }

    /* ============================================================
     * ELIMINAR CONEXIÓN
     * ============================================================ */
    public function destroy(WahaConexion $waha)
    {
        $nombre = $waha->nombre;
        $waha->delete();

        return response()->json([
            'ok' => true,
            'message' => "Conexión «{$nombre}» eliminada correctamente."
        ]);
    }

    /* ============================================================
     * PROBAR CONEXIÓN
     * ============================================================ */
    public function test($id)
    {
        $conexion = WahaConexion::find($id);
        if (!$conexion) return response()->json(['ok'=>false,'message'=>"Conexión no encontrada"],404);

        return $this->fetchSessions($conexion, true);
    }

    /* ============================================================
     * SESIONES EN TIEMPO REAL
     * ============================================================ */
    public function sesionesRealTime($id)
    {
        $conexion = WahaConexion::find($id);
        if (!$conexion) return response()->json(['ok'=>false,'message'=>"Conexión no encontrada"],404);

        return $this->fetchSessions($conexion);
    }

    /* ============================================================
     * FUNCIÓN INTERNA: OBTENER SESSIONS WAHA
     * ============================================================ */
    private function fetchSessions(WahaConexion $waha, bool $normalize = true)
    {
        try {
            $url = rtrim($waha->host,'/').'/api/sessions';

            $response = Http::withOptions(['verify'=>false])
                ->timeout(10)
                ->withHeaders(['x-api-key'=>trim($waha->token_api)])
                ->get($url);

            if ($response->failed()) {
                $waha->update(['estado'=>'error']);
                return response()->json(['ok'=>false,'message'=>'Error del servidor WAHA']);
            }

            $waha->update(['estado'=>'activo']);
            $data = $response->json();

            if ($normalize) {
                $data = collect($data)->map(fn($s)=>[
                    'nombre'=>$s['name']??'Sin nombre',
                    'telefono'=>isset($s['me']['id']) ? explode(':',$s['me']['id'])[0]:'-',
                    'estado'=>strtolower($s['status']??'desconectado'),
                    'enviados'=>0,
                    'pendientes'=>0,
                    'pushName'=>$s['me']['pushName']??'-'
                ]);
            }

            return response()->json(['ok'=>true,'data'=>$data,'estado'=>'activo']);

        } catch (\Throwable $e) {
            $waha->update(['estado'=>'error']);
            Log::error("WAHA {$waha->nombre} error: {$e->getMessage()}");
            return response()->json(['ok'=>false,'message'=>'No se pudo conectar a WAHA'],500);
        }
    }

    /* ============================================================
     * SESIONES DISPONIBLES DE TODAS LAS CONEXIONES
     * ============================================================ */
    public function sesionesDisponibles()
    {
        $todas = collect();
        foreach(WahaConexion::all() as $conexion) {
            try {
                $url = rtrim($conexion->host,'/').'/api/sessions';
                $res = Http::withOptions(['verify'=>false])
                    ->withHeaders(['x-api-key'=>trim($conexion->token_api)])
                    ->timeout(10)
                    ->get($url);

                if ($res->successful()) {
                    $sesiones = collect($res->json())->map(fn($s)=>[
                        'nombre'=>$s['name']??'Sin nombre',
                        'telefono'=>isset($s['me']['id']) ? explode(':',$s['me']['id'])[0]:'-',
                        'estado'=>strtolower($s['status']??'desconectado'),
                        'conexion_nombre'=>$conexion->nombre,
                        'conexion_id'=>$conexion->id,
                        'host'=>$conexion->host,
                        'token_api'=>$conexion->token_api,
                        'pushName'=>$s['me']['pushName']??'-'
                    ]);
                    $todas = $todas->merge($sesiones);
                }
            } catch (\Throwable $e) {
                Log::warning("Error sesiones {$conexion->nombre}: ".$e->getMessage());
            }
        }
        return response()->json(['ok'=>true,'data'=>$todas->values()]);
    }

    /* ============================================================
     * SESIONES FILTRADAS POR USUARIO
     * ============================================================ */
    public function sesionesPorUsuario()
    {
        $user = Auth::user();
        $filtros = ConexionUsuarioFiltro::where('user_id',$user->id)->get();
        $resultado = collect();

        foreach($filtros as $item){
            $conexion = $item->wahaConexion;
            if(!$conexion) continue;

            try {
                $url = rtrim($conexion->host,'/').'/api/sessions';
                $res = Http::withOptions(['verify'=>false])
                    ->withHeaders(['x-api-key'=>trim($conexion->token_api)])
                    ->timeout(10)
                    ->get($url);

                if($res->successful()){
                    $sesiones = collect($res->json())
                        ->filter(fn($s)=> str_contains(strtolower($s['name']??''), strtolower($item->filtro)))
                        ->map(fn($s)=>[
                            'nombre'=>$s['name']??'Sin nombre',
                            'telefono'=>isset($s['me']['id'])?explode(':',$s['me']['id'])[0]:'-',
                            'estado'=>strtolower($s['status']??'desconectado'),
                            'conexion_id'=>$conexion->id,
                            'conexion_nombre'=>$conexion->nombre,
                            'pushName'=>$s['me']['pushName']??'-',
                            'filtro'=>$item->filtro
                        ]);
                    $resultado = $resultado->merge($sesiones);
                }
            } catch (\Throwable $e){
                Log::warning("Error sesiones filtradas {$conexion->nombre}: ".$e->getMessage());
            }
        }

        return response()->json(['ok'=>true,'data'=>$resultado->values()]);
    }

    /* ============================================================
     * ENVIAR MENSAJE A UNA SESIÓN
     * ============================================================ */
    public function enviarMensaje(Request $request)
    {
        $data = $request->validate([
            'conexion_id'=>'required|exists:waha_conexiones,id',
            'numero'=>'required|string',
            'mensaje'=>'required|string',
            'nombre_sesion'=>'required|string'
        ]);

        try{
            $conexion = WahaConexion::findOrFail($data['conexion_id']);
            $url = rtrim($conexion->host,'/').'/api/sendText';
            $payload = [
                'chatId'=>$data['numero'].'@c.us',
                'text'=>$data['mensaje'],
                'session'=>$data['nombre_sesion']
            ];

            $response = Http::withOptions(['verify'=>false])
                ->withHeaders(['x-api-key'=>trim($conexion->token_api)])
                ->post($url,$payload);

            if($response->failed()){
                Log::error("WAHA error ({$conexion->nombre}): ".$response->body());
                return response()->json([
                    'ok'=>false,
                    'message'=>'Error al enviar el mensaje.',
                    'error'=>$response->body()
                ], $response->status());
            }

            return response()->json([
                'ok'=>true,
                'message'=>'Mensaje enviado correctamente.',
                'data'=>$response->json()
            ]);

        }catch(\Throwable $e){
            Log::error("Error interno WAHA: ".$e->getMessage());
            return response()->json([
                'ok'=>false,
                'message'=>'Error interno del servidor.',
                'error'=>$e->getMessage()
            ],500);
        }
    }

    /* ============================================================
     * ASIGNACIONES USUARIO / FILTRO
     * ============================================================ */

    // Listar asignaciones de una conexión
    public function listarAsignaciones($id)
{
    $asignaciones = ConexionUsuarioFiltro::with(['user:id,name','wahaConexion:id,nombre'])
        ->where('waha_conexion_id',$id)
        ->get();

    return response()->json(['data'=>$asignaciones]);
}


    // Crear asignación para una conexión
    public function storeAsignacion(Request $request, $id)
    {
        $data = $request->validate([
            'user_id'=>'required|exists:users,id',
            'filtro'=>'nullable|string|max:150'
        ]);
        $data['waha_conexion_id'] = $id;

        if(ConexionUsuarioFiltro::where('user_id',$data['user_id'])->where('waha_conexion_id',$id)->exists()){
            return response()->json(['ok'=>false,'message'=>'Este usuario ya está asignado a esta conexión.'],422);
        }

        $registro = ConexionUsuarioFiltro::create($data);

        return response()->json([
            'ok'=>true,
            'message'=>'Asignación registrada correctamente.',
            'data'=>$registro->load(['user:id,name','wahaConexion:id,nombre'])
        ]);
    }

    // Eliminar asignación
    public function deleteAsignacion($id)
    {
        $asignacion = ConexionUsuarioFiltro::find($id);
        if(!$asignacion){
            return response()->json(['ok'=>false,'message'=>'Asignación no encontrada.'],404);
        }

        $asignacion->delete();

        return response()->json(['ok'=>true,'message'=>'Asignación eliminada correctamente.']);
    }
}
