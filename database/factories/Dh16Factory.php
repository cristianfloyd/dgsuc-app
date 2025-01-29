<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Mapuche\Dh16;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mapuche\Dh16>
 */
class Dh16Factory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Dh16::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codn_grupo' => fake()->numberBetween(1, 999),
            'codn_conce' => fake()->numberBetween(1, 999),
        ];
    }
}
