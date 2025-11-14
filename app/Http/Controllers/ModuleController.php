<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:modules,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'required|string|max:255|unique:permissions,name',
        ]);

        $module = Module::create(['name' => $data['name']]);

        foreach ($data['permissions'] ?? [] as $permissionName) {
            $module->permissions()->create([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        return response()->json([
            'message' => 'Módulo y permisos creados correctamente.',
            'module' => $module->load('permissions'),
        ]);
    }
}
