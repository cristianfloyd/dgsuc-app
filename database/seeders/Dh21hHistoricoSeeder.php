<?php

namespace Database\Seeders;

use App\Models\Dh01;
use App\Models\Dh03;
use App\Models\Mapuche\Dh21h;
use App\Models\Mapuche\Dh22;
use App\Services\EncodingService;
use Illuminate\Database\Seeder;

class Dh21hHistoricoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtenemos 10 legajos aleatorios únicos de dh01
        $legajosConCargo = Dh03::select('nro_legaj', 'nro_cargo')
            ->whereIn('nro_legaj', function ($query): void {
                $query->select('nro_legaj')
                    ->from('dh01')
                    ->inRandomOrder()
                    ->limit(10);
            })
            ->groupBy('nro_legaj', 'nro_cargo')
            ->get();


        // Definimos los períodos (4 meses hacia atrás desde octubre 2024)
        $periodos = [
            ['ano' => 2024, 'mes' => 10],
            ['ano' => 2024, 'mes' => 9],
            ['ano' => 2024, 'mes' => 8],
            ['ano' => 2024, 'mes' => 7],
        ];

        // Separamos los legajos: 80% con todos los períodos, 20% solo con el primer período
        $legajosCompletos = $legajosConCargo->take(8);
        $legajosIncompletos = $legajosConCargo->skip(2);

        foreach ($periodos as $periodo) {
            // Crear una liquidación definitiva para el período
            $liquidacion = Dh22::create([
                'nro_liqui' => Dh22::max('nro_liqui') + 1,
                'desc_liqui' => EncodingService::toLatin1("Liquidacion Definitiva {$periodo['mes']}/{$periodo['ano']}"),
                'periodo' => $periodo['ano'] . '' . $periodo['mes'],
                'per_anoap' => $periodo['ano'],
                'per_mesap' => $periodo['mes'],
                'fec_emisi' => now(),
                'sino_cerra' => 'N',
                'id_tipo_liqui' => 1,
            ]);



            // Procesar legajos completos
            if ($legajosCompletos->count() > 0) {
                foreach ($legajosCompletos as $legajo) {
                    Dh21h::factory()->create([
                        'nro_liqui' => $liquidacion->nro_liqui,
                        'nro_legaj' => $legajo->nro_legaj,
                        'nro_cargo' => $legajo->nro_cargo,
                        'ano_retro' => $periodo['ano'],
                        'mes_retro' => $periodo['mes'],
                    ]);
                }
            }

            // Procesar legajos incompletos solo para julio 2024
            if ($periodo['mes'] == 7 && $legajosIncompletos->count() > 0) {
                foreach ($legajosIncompletos as $legajo) {
                    Dh21h::factory()->create([
                        'nro_liqui' => $liquidacion->nro_liqui,
                        'nro_legaj' => $legajo->nro_legaj,
                        'nro_cargo' => $legajo->nro_cargo,
                        'ano_retro' => $periodo['ano'],
                        'mes_retro' => $periodo['mes'],
                    ]);
                }
            }
        }
    }
}
