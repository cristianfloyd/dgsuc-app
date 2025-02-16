<?php

namespace Database\Factories;

use App\Models\RepEmbarazada;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RepEmbarazada>
 */
class RepEmbarazadaFactory extends Factory
{
    protected $model = RepEmbarazada::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nro_legaj' => $this->faker->unique()->numberBetween(1000, 9999),
            'apellido' => str_pad($this->faker->lastName(), 20),
            'nombre' => str_pad($this->faker->firstName(), 20),
            'cuil' => $this->faker->numerify('##-########-#'),
            'codc_uacad' => $this->faker->regexify('[A-Z]{4}'),
        ];
    }
}
