<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Compania;

class CompaniaSeeder extends Seeder
{
    public function run(): void
    {
        $companias = [
            ['nombre' => 'Entel', 'descripcion' => 'Operadora de telecomunicaciones.'],
            ['nombre' => 'Claro', 'descripcion' => 'Proveedor de servicios móviles e internet.'],
            ['nombre' => 'Movistar', 'descripcion' => 'Empresa de telefonía e internet.'],
            ['nombre' => 'Bitel', 'descripcion' => 'Compañía de telecomunicaciones del Perú.'],
        ];

        foreach ($companias as $compania) {
            Compania::create($compania);
        }
    }
}
