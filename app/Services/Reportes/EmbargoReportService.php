<?php
declare(strict_types=1);

namespace App\Services\Reportes;

use App\Models\Mapuche\Embargo;
use Illuminate\Support\Facades\Log;
use App\Models\Reportes\EmbargoReportModel;
use Illuminate\Database\Eloquent\Collection;

class EmbargoReportService
{
    public function generateReport(?int $nro_liqui = 2): Collection
    {
        try {
            // Primero obtenemos los embargos activos
            $embargos = $this->getActiveEmbargos();

            // Agrupamos por legajo y calculamos importes
            $processedData = $this->processEmbargos($embargos, $nro_liqui);

            // Actualizamos el modelo Sushi con los datos procesados
            //EmbargoReportModel::setReportData($processedData->toArray());

            return $processedData;
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
    private function getActiveEmbargos(): Collection
    {
        return Embargo::query()
            ->with([
                'datosPersonales',
                'tipoEmbargo',
                'datosPersonales.dh03'
            ])
            ->whereHas('estado', fn($query) =>
                $query->where('id_estado_embargo', 2)
            )
            // ->whereIn('nro_legaj', [149639,159300,164859])
            ->get();
    }

    /**
     * Procesa los embargos y genera los registros del reporte
     */
    private function processEmbargos(Collection $embargos, int $nro_liqui): Collection
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
