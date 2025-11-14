<?php

namespace Database\Factories;

use App\Models\Compania;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chip>
 */
class ChipFactory extends Factory
{
    public function definition(): array
    {
        return [
            'numero' => $this->faker->unique()->numerify('9########'),
            'compania_id' => Compania::inRandomOrder()->value('id') ?? Compania::factory(),
            'tiene_senal' => $this->faker->boolean(), // true o false aleatorio
            'bloqueado' => $this->faker->boolean(20), // 20% probabilidad de estar bloqueado
        ];
    }
}
