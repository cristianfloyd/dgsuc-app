<?php

namespace App\Services\Reportes;

use App\Services\Mapuche\DosubaReportService;
use Illuminate\Database\Eloquent\Builder;

class DosubaIntegradorService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly LegajosSinLiquidarService $legajosSinLiquidarService,
        private readonly DosubaReportService $dosubaReportService,
    ) {
    }

    public function getLegajosSinLiquidarConDosuba(): Builder
    {
        // Obtenemos los legajos sin liquidar
        $queryLegajosSinLiquidar = $this->legajosSinLiquidarService->getLegajosSinLiquidar();
        dump($queryLegajosSinLiquidar);
        // Obtenemos los CUIL del reporte DOSUBA
        $cuilsDosuba = $this->dosubaReportService->getDosubaReport()
            ->pluck('CUIL')
            ->toArray();

        // Agregamos el filtro de CUIL a la query original
        return $queryLegajosSinLiquidar->whereIn('dh21h.cuil', $cuilsDosuba);
    }
}
