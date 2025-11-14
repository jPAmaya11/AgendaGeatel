<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Chip;
use App\Models\Estado;
use App\Models\Usuario;

class RegistroFactory extends Factory
{
    public function definition(): array
    {
        return [
            'chip_id' => Chip::inRandomOrder()->first()?->id ?? Chip::factory(),
            'estado_id' => Estado::inRandomOrder()->first()?->id ?? Estado::factory(),
            'usuario_id' => Usuario::inRandomOrder()->first()?->id ?? Usuario::factory(),
            'conteo_cambios' => $this->faker->numberBetween(0, 10),
            'fecha_revision' => $this->faker->date(),
        ];
    }
}
