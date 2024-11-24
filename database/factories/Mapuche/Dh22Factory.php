<?php

namespace Database\Factories\Mapuche;

use App\Models\Mapuche\Dh22;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mapuche\Dh22>
 */
class Dh22Factory extends Factory
{
    protected $model = Dh22::class;


    public function definition(): array
    {
        // Obtener el último nro_liqui y sumar 1
        $ultimoNroLiqui = Dh22::max('nro_liqui') ?? 0;
        $siguienteNroLiqui = $ultimoNroLiqui + 1;

        return [
            'nro_liqui' => $siguienteNroLiqui,
            'desc_liqui' => 'Liquidación Definitiva ' . fake()->monthName(),
            'periodo' => fake()->date(),
            'sino_cerra' => 'N',
            'id_tipo_liqui' => 1
        ];
    }
}
