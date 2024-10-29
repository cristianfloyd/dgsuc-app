<?php

namespace Database\Factories\Mapuche;

use App\Models\Mapuche\Dhr1;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mapuche\Dhr1>
 */
class Dhr1Factory extends Factory
{
    protected $model = Dhr1::class;

    public function definition(): array
    {
        return [
            'nro_liqui' => $this->faker->unique()->randomNumber(),
            'per_liano' => $this->faker->year(),
            'per_limes' => $this->faker->numberBetween(1, 12),
            'desc_liqui' => $this->faker->text(20),
            'fec_emisi' => $this->faker->date(),
            'fec_ultap' => $this->faker->date(),
            'per_anoap' => $this->faker->year(),
            'per_mesap' => $this->faker->numberBetween(1, 12),
            'desc_lugap' => $this->faker->text(20),
            'plantilla' => null,
        ];
    }
}
