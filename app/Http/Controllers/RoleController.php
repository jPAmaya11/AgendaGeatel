<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Cartera;
use App\Models\Reporte;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Permission;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    /**
     * Display the roles index with all roles, carteras, reportes y permisos.
     */
    public function index(): Response
    {
        return Inertia::render('GestionarRoles', [
            'roles'       => Role::with(['carteras', 'reportes', 'permissions'])->get(),
            'carteras'    => Cartera::all(),
            'reportes'    => Reporte::with('cartera')->get(),
            'permissions' => Permission::with('module')->get(),
            'success'     => session('success'),
        ]);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        Log::debug('📥 Datos recibidos en store', $request->all());

        try {
            $data = $request->validate([
                'name'        => 'required|string|max:255|unique:roles,name',
                'carteras'    => 'nullable|array',
                'carteras.*'  => 'exists:carteras,id',
                'reportes'    => 'nullable|array',
                'reportes.*'  => 'exists:reportes,id',
                'permissions' => 'nullable|array',
                'permissions.*' => 'exists:permissions,name',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error(' Validación fallida', $e->errors());
            throw $e; // Re-lanza para que Inertia los reciba
        }

        $role = Role::create([
            'name'       => $data['name'],
            'guard_name' => 'web',
        ]);

        // Sincronizar relaciones pivot
        $role->carteras()->sync($data['carteras'] ?? []);
        $role->reportes()->sync($data['reportes'] ?? []);

        // Sincronizar permisos
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()
            ->route('roles.index')
            ->with('success', "Rol «{$role->name}» creado correctamente.");
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($role->id),
            ],
            'carteras'    => 'nullable|array',
            'carteras.*'  => 'exists:carteras,id',
            'reportes'    => 'nullable|array',
            'reportes.*'  => 'exists:reportes,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $role->carteras()->sync($data['carteras'] ?? []);
        $role->reportes()->sync($data['reportes'] ?? []);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()
            ->route('roles.index')
            ->with('success', "Rol «{$role->name}» actualizado correctamente.");
    }


    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        $name = $role->name;
        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', "Rol «{$name}» eliminado correctamente.");
    }
}
