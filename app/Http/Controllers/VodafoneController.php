<?php

namespace App\Http\Controllers;

use App\Models\Vodafone;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class VodafoneController extends Controller
{

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = Vodafone::query()->with(['user.roles']); // Incluye los roles del usuario


        if (!$user->can('vodafone.ver-global')) {
            $query->where('user_id', $user->id);
        }

        $search = $request->input('search');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_cliente', 'like', "%$search%")
                ->orWhere('telefono_contacto', 'like', "%$search%")
                ->orWhere('dni_nif_cif', 'like', "%$search%");
            });
        }

        $items = $query->oldest()->paginate(17)->withQueryString();


        return Inertia::render('Vodafone', [
            'items' => $items,
            'success' => session('success'),
            'canViewGlobal' => $user->can('vodafone.ver-global'), // Envía el permiso
            'filters' => [ 'search' => $search ]
        ]);
    }



    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules());


        $user = Auth::user();
        $data['user_id'] = $user->id;

        Vodafone::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Datos creados correctamente.',
                'vodafone' => $data,
            ]);
        }

        return redirect()->route('vodafone.index')->with('success', 'Registro creado correctamente.');
    }



    public function update(Request $request, Vodafone $vodafone)
    {
        $data = $request->validate($this->validationRules());
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $data['user_id'] = $user->id;



        $vodafone->update($data);
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Datos actualizados correctamente.',
                'vodafone' => $data,
            ]);
        }


        return redirect()->route('vodafone.index')->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(Request $request, Vodafone $vodafone)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($vodafone->user_id !== $user->id && !$user->can('vodafone.destroy')) {
            abort(403, 'No autorizado defefef');
        }

        $vodafone->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data eliminada correctamente.',
            ]);
        }

        return redirect()->route('vodafone.index')->with('success', 'Registro eliminado correctamente.');
    }

    private function validationRules(): array
    {
        return [
            'dni_nif_cif' => 'nullable|string|max:255',
            'id_cliente' => 'nullable|string|max:255',
            'observacion_smart' => 'nullable|string',
            'oferta_comercial' => 'nullable|string',
            'operador_actual' => 'nullable|string|max:255',
            'telefono_contacto' => 'nullable|string|max:20',
            'nombre_cliente' => 'nullable|string|max:255',
            'direccion_instalacion' => 'nullable|string',
            'fecha_creacion' => 'nullable|date',
            'fecha_cierre' => 'nullable|date',
            'observaciones_back_office' => 'nullable|string',
            'tipificaciones' => 'nullable|string',
            'observaciones_operaciones' => 'nullable|string',
        ];
    }
}
