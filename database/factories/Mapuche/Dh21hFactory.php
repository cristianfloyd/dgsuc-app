<?php

namespace Database\Factories\Mapuche;

use App\Models\Mapuche\Dh22;
use App\Models\Mapuche\Dh21h;
use App\Models\Mapuche\Catalogo\Dh30;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mapuche\Dh21h>
 */
class Dh21hFactory extends Factory
{
    /**
     * El modelo asociado al factory.
     *
     * @var string
     */
    protected $model = Dh21h::class;


    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Obtener códigos de unidades académicas válidos del catálogo
        $codigosUacad = Dh30::where('nro_tabla', 13)
            ->pluck('desc_abrev')
            ->toArray();

        // Obtener el último nro_liqui de dh22
        $ultimoNroLiqui = Dh22::max('nro_liqui') ?? 0;
        $siguienteNroLiqui = $ultimoNroLiqui + 1;

        


        return [
            'nro_liqui' => $siguienteNroLiqui,
            'nro_legaj' => fake()->numberBetween(10000, 99999),
            'nro_cargo' => fake()->numberBetween(1, 5),
            'codn_conce' => 101,
            'impp_conce' => fake()->numberBetween(200000, 4000000),
            'tipo_conce' => 'H',
            'nov1_conce' => 0,
            'nov2_conce' => 0,
            'nro_orimp' => 1,
            'tipoescalafon' => 'D',
            'nrogrupoesc' => 1,
            'codigoescalafon' => str_pad(fake()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'codc_regio' => '1',
            'codc_uacad' => fake()->randomElement($codigosUacad),
            'codn_area' => str_pad(fake()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT),
            'codn_subar' => str_pad(fake()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT),
            'codn_fuent' => str_pad(fake()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT),
            'codn_progr' => str_pad(fake()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT),
            'codn_subpr' => str_pad(fake()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT),
            'codn_proye' => str_pad(fake()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT),
            'codn_activ' => str_pad(fake()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT),
            'codn_obra' => str_pad(fake()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT),
            'codn_final' => str_pad(fake()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT),
            'codn_funci' => str_pad(fake()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT),
            'ano_retro' => 0,
            'mes_retro' => 0,
            'detallenovedad' => null,
            'codn_grupo_presup' => str_pad(fake()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT),
            'tipo_ejercicio' => 'D',
            'codn_subsubar' => str_pad(fake()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT),
        ];
    }

    /**
     * Estado para liquidaciones definitivas
     */
    public function definitiva()
    {
        return $this->state(function (array $attributes) {
            // Primero creamos una liquidación definitiva en dh22
            $liquidacion = Dh22::factory()->create([
                'desc_liqui' => 'Liquidación Definitiva ' . fake()->monthName(),
                'nro_liqui' => $attributes['nro_liqui'],
                'sino_cerra' => 'N',
                'id_tipo_liqui' => 1,
            ]);

            return [
                'nro_liqui' => $liquidacion->nro_liqui,
                'sino_cerra' => 'N'
            ];
        });
    }

    /**
     * Estado para un período específico
     */
    public function periodo(int $año, int $mes)
    {
        return $this->state(function (array $attributes) use ($año, $mes) {
            return [
                'ano_retro' => $año,
                'mes_retro' => $mes,
            ];
        });
    }
}
