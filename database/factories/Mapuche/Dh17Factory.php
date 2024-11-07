<?php

namespace Database\Factories\Mapuche;

use App\Models\Mapuche\Dh17;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mapuche\Dh17>
 */
class Dh17Factory extends Factory
{
    protected $model = Dh17::class;

    public function definition(): array
    {
        return [
            'codn_conce' => $this->faker->unique()->numberBetween(1, 9999),
            'objt_gtope' => $this->faker->optional()->text(30),
            'objt_gtote' => $this->faker->optional()->text(30),
            'nro_prove' => $this->faker->optional()->numberBetween(1, 9999),
        ];
    }
}
