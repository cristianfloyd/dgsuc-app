<?php

namespace Database\Factories\Mapuche;

use App\Models\Mapuche\Dhr3;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mapuche\Dhr3>
 */
class Dhr3Factory extends Factory
{
    protected $model = Dhr3::class;

    public function definition(): array
    {
        return [
            'nro_liqui' => $this->faker->numberBetween(1, 9999),
            'nro_legaj' => $this->faker->numberBetween(1, 9999),
            'nro_cargo' => $this->faker->numberBetween(1, 99),
            'codc_hhdd' => $this->faker->randomLetter(),
            'nro_renglo' => $this->faker->numberBetween(1, 999),
            'nro_conce' => $this->faker->numberBetween(1, 999),
            'desc_conc' => $this->faker->text(30),
            'novedad1' => $this->faker->randomFloat(2, 0, 9999),
            'novedad2' => $this->faker->randomFloat(2, 0, 9999),
            'impo_conc' => $this->faker->randomFloat(2, 0, 99999),
            'ano_retro' => $this->faker->year(),
            'mes_retro' => $this->faker->numberBetween(1, 12),
            'nro_recibo' => $this->faker->unique()->numberBetween(1, 9999),
            'observa' => $this->faker->text(20),
            'tipo_conce' => $this->faker->randomElement(['HAB', 'DESC', 'APOR']),
        ];
    }
}
