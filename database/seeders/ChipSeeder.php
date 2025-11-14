<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Estado;

class ChipSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            ['nombre' => 'Activo', 'descripcion' => 'El chip está operativo'],
            ['nombre' => 'Inactivo', 'descripcion' => 'El chip está fuera de servicio'],
            ['nombre' => 'Suspendido', 'descripcion' => 'El chip fue suspendido temporalmente'],
            ['nombre' => 'Bloqueado', 'descripcion' => 'El chip está bloqueado permanentemente'],
        ];

        foreach ($estados as $estado) {
            Estado::create($estado);
        }
    }
}
