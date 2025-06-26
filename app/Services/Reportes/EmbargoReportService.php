<?php
declare(strict_types=1);

namespace App\Services\Reportes;

use App\Models\Mapuche\Embargo;
use App\Services\EncodingService;
use Illuminate\Support\Facades\DB;
use App\Enums\ConceptosEmbargoEnum;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use App\Models\Reportes\EmbargoReportModel;
use Illuminate\Database\Eloquent\Collection;

class EmbargoReportService
{
    use MapucheConnectionTrait;

    public function generateReport(?int $nro_liqui): Collection
    {
        $connection = DB::connection($this->getConnectionName());
        Log::info("Generando reporte de embargos para la liquidación: $nro_liqui");
        try {
            // Aseguramos que la tabla existe
            EmbargoReportModel::createTableIfNotExists();

            // Limpiamos registros antiguos
            EmbargoReportModel::cleanOldRecords();

            $results = $connection->query()
            ->fromSub(function ($query) {
                $query->from('mapuche.emb_embargo as e')
                    ->select([
                        'e.nro_legaj',
                        'e.nom_demandado',
                        'e.codn_conce',
                        'e.nro_embargo',
                        DB::connection($this->getConnectionName())->raw("CONCAT(e.nro_embargo, '-', e.nro_oficio) as detallenovedad"),
                        'e.caratula'
                    ]);
            }, 'embargos')
            ->join('mapuche.dh03 as d', function ($join) {
                $join->on('d.nro_legaj', '=', 'embargos.nro_legaj')
                    ->where('d.chkstopliq', '=', 0)
                    ->whereNotNull('d.nro_legaj');
            })
            ->Join('mapuche.dh21 as d2', function ($join) use ($nro_liqui) {
                $join->on('d2.nro_legaj', '=', 'embargos.nro_legaj')
                    ->on('d2.nro_cargo', '=', 'd.nro_cargo')
                    ->on('d2.codn_conce', '=', 'embargos.codn_conce')
                    ->on('d2.detallenovedad', '=', 'embargos.detallenovedad')
                    ->where('d2.nro_liqui', '=', $nro_liqui)
                    ->whereNotNull('d2.impp_conce');
            })
            ->select([
                'embargos.nro_legaj',
                'd.nro_cargo',
                'embargos.nom_demandado',
                'd.codc_uacad',
                'embargos.caratula',
                'embargos.codn_conce',
                'd2.impp_conce',
                'd2.nov1_conce',
                'd2.nov2_conce',
                'embargos.nro_embargo',
                'embargos.detallenovedad',
                'd2.nro_liqui'
            ])
            ->where('d2.nro_liqui', '=', $nro_liqui)
            ->distinct()
            ->orderBy('embargos.nro_legaj')
            ->orderBy('d.nro_cargo')
            ->orderBy('embargos.codn_conce')
            ->get();

            $embargos = $results->map(function ($item) {
                $item->caratula = EncodingService::toUtf8($item->caratula);
                $item->nom_demandado = EncodingService::toUtf8($item->nom_demandado);
                return $item;
            });

            // Obtener los importes del concepto 861 para los legajos
            $importes861 = DB::connection($this->getConnectionName())
                ->table('mapuche.dh21')
                ->whereIn('nro_legaj', $embargos->pluck('nro_legaj'))
                ->where('codn_conce', ConceptosEmbargoEnum::CONCEPTO_861->value)
                ->where('nro_liqui', $nro_liqui)
                ->pluck('impp_conce', 'nro_legaj');

            // Obtener los conceptos adicionales en una sola consulta
            $conceptosAdicionales = DB::connection($this->getConnectionName())
                ->table('mapuche.dh21')
                ->select(
                    'nro_legaj',
                    'nro_cargo',
                    DB::raw('SUM(CASE WHEN codn_conce = ' . ConceptosEmbargoEnum::REMUNERATIVO->value . ' THEN impp_conce ELSE 0 END) as remunerativo'),
                    DB::raw('SUM(CASE WHEN codn_conce = ' . ConceptosEmbargoEnum::CONCEPTO_860->value . ' THEN impp_conce ELSE 0 END) as concepto_860'),
                    DB::raw('SUM(CASE WHEN codn_conce = ' . ConceptosEmbargoEnum::CONCEPTO_861->value . ' THEN impp_conce ELSE 0 END) as concepto_861')
                )
                ->whereIn('nro_legaj', $embargos->pluck('nro_legaj')->unique())
                ->whereIn('nro_cargo', $embargos->pluck('nro_cargo')->unique())
                ->where('nro_liqui', $nro_liqui)
                ->groupBy('nro_legaj', 'nro_cargo')
                ->get()
                ->keyBy(fn($item) => "{$item->nro_legaj}-{$item->nro_cargo}");

            // Agregar los conceptos adicionales al resultado
            $embargos = $embargos->map(function ($item) use ($conceptosAdicionales) {
                $key = $item->nro_legaj . '-' . $item->nro_cargo;
                $conceptos = $conceptosAdicionales[$key] ?? null;

                $item->remunerativo = $conceptos ? $conceptos->remunerativo : 0;
                $item->{'860'} = $conceptos ? $conceptos->concepto_860 : 0;
                $item->{'861'} = $conceptos ? $conceptos->concepto_861 : 0;

                return $item;
            });


            // convertir a eloquent collection
            return new Collection($embargos);
        } catch (\Exception $e) {
            Log::error('Error generando reporte de embargos', [
                'error' => $e->getMessage(),
                'nro_liqui' => $nro_liqui
            ]);
            throw $e;
        }
    }

    /**
     * Obtiene los embargos activos con sus relaciones
     */
    public function getActiveEmbargos(): Collection
    {
        return Embargo::query()
            ->with([
                'datosPersonales',
                'tipoEmbargo',
                'datosPersonales.dh03'
            ])
            // ->whereIn('nro_legaj', [149639,159300,164859])
            ->get();
    }





    /**
     * @param Collection $embargos
     * @param int $nro_liqui
     * @return
     */
    public function getEmbargos(Collection $embargos, int $nro_liqui)
    {
        return $embargos
        ->groupBy('nro_legaj')
        ->map(function ($embargosLegajo) use ($nro_liqui) {
            return $embargosLegajo->map(function ($embargo) use ($nro_liqui) {
                $importes = $embargo->getImporteDescontado($nro_liqui);

                return $importes->map(function ($importe) use ($embargo) {
                    return [
                        'nro_legaj' => $embargo->nro_legaj,
                        'nombre_completo' => $embargo->datosPersonales->nombre_completo,
                        'codn_conce' => $embargo->tipoEmbargo->codn_conce,
                        'importe_descontado' => $importe->impp_conce,
                        'nro_embargo' => $embargo->nro_embargo,
                        'nro_cargo' => $importe->nro_cargo,
                        'caratula' => $embargo->caratula,
                        'codc_uacad' => $embargo->datosPersonales->dh03()
                            ->where('nro_cargo', $importe->nro_cargo)
                            ->value('codc_uacad')
                    ];
                });
            })->flatten(1);
        })->flatten(1);
    }
}
