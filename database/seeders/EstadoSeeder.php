<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Estado;

class EstadoSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            ['nombre' => 'Activo', 'descripcion' => 'El número está activo y operativo.'],
            ['nombre' => 'Inactivo', 'descripcion' => 'El número no está operativo temporalmente.'],
            ['nombre' => 'Sin señal', 'descripcion' => 'El chip no tiene señal disponible.'],
            ['nombre' => 'Bloqueado', 'descripcion' => 'El chip fue bloqueado o deshabilitado.'],
        ];

        foreach ($estados as $estado) {
            Estado::create($estado);
        }
    }
}
