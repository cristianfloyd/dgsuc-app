<?php

namespace Database\Factories\Mapuche;

use App\Models\Mapuche\Dha8;
use Illuminate\Database\Eloquent\Factories\Factory;

class Dha8Factory extends Factory
{
    protected $model = Dha8::class;

    public function definition(): array
    {
        return [
            'nro_legajo' => $this->faker->unique()->numberBetween(1000, 99999),
            'codigosituacion' => $this->faker->numberBetween(1, 10),
            'codigocondicion' => $this->faker->numberBetween(1, 5),
            'codigoactividad' => $this->faker->numberBetween(1, 20),
            'codigozona' => $this->faker->numberBetween(1, 5),
            'porcaporteadicss' => $this->faker->randomFloat(2, 0, 100),
            'codigomodalcontrat' => $this->faker->numberBetween(1, 3),
            'provincialocalidad' => $this->faker->city(),
        ];
    }
}
