<?php

namespace Database\Factories;

use App\Models\Suc\RetTablaBasicosConcesp;
use Illuminate\Database\Eloquent\Factories\Factory;

class RetTablaBasicosConcespFactory extends Factory
{
    protected $model = RetTablaBasicosConcesp::class;

    public function definition()
    {
        return [
            'fecha_desde' => $this->faker->date(),
            'fecha_hasta' => $this->faker->date(),
            'cat_id' => $this->faker->regexify('[A-Z0-9]{4}'),
            'conc_liq_id' => $this->faker->regexify('[A-Z0-9]{3}'),
            'monto' => $this->faker->randomFloat(2, 0, 10000),
            'anios' => $this->faker->numberBetween(0, 50),
        ];
    }
}
