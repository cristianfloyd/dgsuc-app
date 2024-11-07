<?php

namespace Database\Factories\Mapuche;

use App\Models\Mapuche\Dh35;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mapuche\Dh35>
 */
class Dh35Factory extends Factory
{
    protected $model = Dh35::class;

    public function definition(): array
    {
        return [
            'tipo_escal' => $this->faker->randomLetter(),
            'codc_carac' => $this->faker->unique()->regexify('[A-Z]{4}'),
            'desc_grupo' => $this->faker->optional()->text(20),
            'tipo_carac' => $this->faker->randomElement(['P', 'T']),
            'nro_orden' => $this->faker->numberBetween(0, 29),
            'nro_subpc' => $this->faker->optional()->numberBetween(1, 100),
            'controlcargos' => $this->faker->optional()->numberBetween(0, 1),
            'controlhoras' => $this->faker->optional()->numberBetween(0, 1),
            'controlpuntos' => $this->faker->optional()->numberBetween(0, 1),
            'caracter_concursado' => $this->faker->boolean(),
        ];
    }
}
