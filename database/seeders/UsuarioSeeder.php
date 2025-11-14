<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            ['nombre' => 'Aaron', 'email' => 'aaron@example.com'],
            ['nombre' => 'Juan', 'email' => 'juan@example.com'],
            ['nombre' => 'George', 'email' => 'george@example.com'],
            ['nombre' => 'Jairo', 'email' => 'jairo@example.com'],
            ['nombre' => 'Cristofer', 'email' => 'cristofer@example.com'],
            ['nombre' => 'Nicolas', 'email' => 'nicolas@example.com'],
        ];

        foreach ($usuarios as $usuario) {
            Usuario::updateOrCreate(
                ['email' => $usuario['email']],
                [
                    'nombre' => $usuario['nombre'],
                    'password' => Hash::make('password123'),
                ]
            );
        }
    }
}
