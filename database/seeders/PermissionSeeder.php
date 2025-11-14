<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modulosConPermisos = [
            'Roles' => ['ver', 'crear', 'guardar', 'editar',  'eliminar'],
            'Usuarios' => ['ver', 'crear', 'guardar', 'editar', 'eliminar'],
            'Carteras' => ['ver', 'crear', 'guardar', 'editar',  'eliminar'],
            'Reportes' => ['ver', 'crear', 'guardar', 'editar',  'eliminar'],
            'Vodafone' => ['ver', 'crear', 'guardar', 'editar', 'eliminar', 'ver-global'],
            'Waha' => ['ver', 'crear', 'guardar', 'editar', 'eliminar'],
        ];

        foreach ($modulosConPermisos as $moduloNombre => $acciones) {
            $modulo = Module::firstOrCreate(['name' => $moduloNombre]);
            $moduloSlug = strtolower($moduloNombre);

            foreach ($acciones as $accion) {
                $permisoNombre = "{$moduloSlug}.{$accion}";
                Permission::firstOrCreate([
                    'name' => $permisoNombre,
                    'guard_name' => 'web',
                    'module_id' => $modulo->id,
                ]);
            }
        }

        // Crear roles
        $rolAdmin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        $rolAdmin->syncPermissions(Permission::all());

        $rolViewer = Role::firstOrCreate([
            'name' => 'viewer',
            'guard_name' => 'web',
        ]);
        $rolViewer->syncPermissions([]); // sin permisos

        $rolSupervisor = Role::firstOrCreate([
            'name' => 'supervisor vodafone',
            'guard_name' => 'web',
        ]);
        $rolSupervisor->syncPermissions([
            'vodafone.ver',
            'vodafone.editar',
            'vodafone.guardar',
            'vodafone.ver-global',
        ]);
    }
}
