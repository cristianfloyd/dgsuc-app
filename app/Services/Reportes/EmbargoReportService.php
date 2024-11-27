<?php
declare(strict_types=1);

namespace App\Services\Reportes;

use App\Models\Dh21;
use App\Models\Mapuche\Embargo;
use Illuminate\Support\Collection;

class EmbargoReportService
{
    public function generateReport(int $nro_liqui = 3): Collection
    {
        // Primero obtenemos los embargos activos
        $embargos = Embargo::query()
            ->with(['datosPersonales', 'tipoEmbargo'])
            ->whereHas('estado', fn($query) => $query->where('id_estado_embargo', 2))
            ->get();

        // Verificamos los descuentos en DH21
        $descuentos = Dh21::query()
            ->where('nro_liqui', $nro_liqui)
            ->whereIn('nro_legaj', $embargos->pluck('nro_legaj'))
            ->whereIn('codn_conce', $embargos->pluck('tipoEmbargo.codn_conce'))
            ->get();

        // Agrupamos por legajo y calculamos importes
        return $embargos
            ->groupBy('nro_legaj')
            ->map(function ($embargosLegajo) use ($nro_liqui) {
                return $embargosLegajo->map(function ($embargo) use ($nro_liqui) {
                    $importeDescontado = $embargo->getImporteDescontado($nro_liqui);

                    return [
                        'nro_legaj' => $embargo->nro_legaj,
                        'nombre_completo' => $embargo->datosPersonales->nombre_completo,
                        'codn_conce' => $embargo->tipoEmbargo->codn_conce,
                        'importe_descontado' => $importeDescontado,
                        'nro_embargo' => $embargo->nro_embargo
                    ];
                });
            });
    }
}