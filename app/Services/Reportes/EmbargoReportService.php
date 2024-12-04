<?php
declare(strict_types=1);

namespace App\Services\Reportes;

use App\Models\Mapuche\Embargo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use App\Models\Reportes\EmbargoReportModel;
use Illuminate\Database\Eloquent\Collection;

class EmbargoReportService
{
    use MapucheConnectionTrait;

    public function generateReport(?int $nro_liqui = 1): Collection
    {
        $connection = $this->getConnectionName();
        try {
            // Aseguramos que la tabla existe
            EmbargoReportModel::createTableIfNotExists();

            // Limpiamos registros antiguos
            EmbargoReportModel::cleanOldRecords();

            return DB::query()
            ->fromSub(function ($query) {
                $query->from('mapuche.emb_embargo as e')
                    ->select([
                        'e.nro_legaj',
                        'e.nom_demandado',
                        'e.codn_conce',
                        'e.nro_embargo',
                        DB::raw("CONCAT(e.nro_embargo, '-', e.nro_oficio) as detallenovedad"),
                        'e.caratula'
                    ]);
            }, 'embargos')
            ->join('mapuche.dh03 as d', function ($join) {
                $join->on('d.nro_legaj', '=', 'embargos.nro_legaj')
                    ->where('d.chkstopliq', '=', 0)
                    ->whereNotNull('d.nro_legaj');
            })
            ->leftJoin('mapuche.dh21 as d2', function ($join) use ($nro_liqui) {
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
                'embargos.nro_embargo',
                'embargos.detallenovedad',
                'd2.nro_liqui'
            ])
            ->distinct()
            ->orderBy('embargos.nro_legaj')
            ->orderBy('d.nro_cargo')
            ->orderBy('embargos.codn_conce')
            ->get();
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
     * Procesa los embargos y genera los registros del reporte
     */
    public function processEmbargos(Collection $embargos, int $nro_liqui): Collection
    {
        $processedData = $embargos
            ->groupBy('nro_legaj')
            ->map(function ($embargosLegajo) use ($nro_liqui) {
                return $embargosLegajo->map(function ($embargo) use ($nro_liqui) {
                    return $this->processEmbargoImportes($embargo, $nro_liqui);
                })->flatten(1);
            })->flatten(1);

        return new Collection($processedData);
    }

    /**
     * Procesa los importes de un embargo específico
     */
    private function processEmbargoImportes($embargo, int $nro_liqui): Collection
    {
        $importes = $embargo->getImporteDescontado($nro_liqui);

        $data = $importes->map(function ($importe) use ($embargo) {
            // Creamos un array asociativo en lugar de una instancia del modelo
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

        return new Collection($data);
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
