<?php

namespace App\Services\Reports;

use Exception;
use App\Traits\ReportCacheTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Data\Responses\SicossReporteData;
use App\Data\Responses\SicossTotalesData;
use App\Models\Mapuche\MapucheSicossReporte;
use App\Services\Mapuche\PeriodoFiscalService;

class SicossReporteService
{
    use ReportCacheTrait;

    protected const REPORT_NAME = 'sicoss';

    protected PeriodoFiscalService $periodoFiscalService;

    public function __construct(PeriodoFiscalService $periodoFiscalService)
    {
        $this->periodoFiscalService = $periodoFiscalService;
    }

    /**
     * Obtiene los datos del reporte SICOSS.
     *
     * @param string $anio
     * @param string $mes
     * @return Collection<SicossReporteData>
     */
    public function getReporteData(string $anio, string $mes): Collection
    {
        // return $this->rememberReportCache(
        //     self::REPORT_NAME,
        //     'data',
        //     [$anio, $mes],
        //     fn() => MapucheSicossReporte::query()
        //         ->getReporte($anio, $mes)
        //         ->get()
        //         ->map(fn($item) => SicossReporteData::fromModel($item))
        // );
        return MapucheSicossReporte::query()
            ->getReporte($anio, $mes)
            ->get()
            ->map(fn($item) => SicossReporteData::fromModel($item));
    }

    /**
     * Obtiene los totales del reporte SICOSS.
     *
     * @param string $anio
     * @param string $mes
     * @return SicossTotalesData
     */
    public function getTotales(string $anio, string $mes): SicossTotalesData
    {
        try {
            // Descomentar para usar caché si es necesario
            // return $this->rememberReportCache(
            //     self::REPORT_NAME,
            //     'totals',
            //     [$anio, $mes],
            //     function () use ($anio, $mes) {
            //         $totales = MapucheSicossReporte::query()->getTotales($anio, $mes);
            //         return SicossTotalesData::fromArray($totales->toArray());
            //     }
            // );
            
            $totales = MapucheSicossReporte::query()->getTotales($anio, $mes);
            
            // Verificar si $totales es un objeto Collection o un array
            if (is_array($totales)) {
                return SicossTotalesData::fromArray($totales);
            } else {
                return SicossTotalesData::fromArray($totales->toArray());
            }
        } catch (Exception $e) {
            Log::error('Error al obtener totales del reporte SICOSS', [
                'error' => $e->getMessage(),
                'anio' => $anio,
                'mes' => $mes,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Devolver valores por defecto en caso de error
            return SicossTotalesData::fromArray([
                'total_aportes' => 0,
                'total_contribuciones' => 0,
                'total_remunerativo' => 0,
                'total_no_remunerativo' => 0,
                'total_c305' => 0,
                'total_c306' => 0,
            ]);
        }
    }

    /**
     * Invalida el caché del reporte para un período específico.
     *
     * @param string $anio
     * @param string $mes
     * @return void
     */
    public function invalidateCache(string $anio, string $mes): void
    {
        $this->forgetReportCache(self::REPORT_NAME, 'data', [$anio, $mes]);
        $this->forgetReportCache(self::REPORT_NAME, 'totals', [$anio, $mes]);
    }

    /**
     * Invalida todo el caché del reporte SICOSS.
     *
     * @return void
     */
    public function invalidateAllCache(): void
    {
        $this->forgetAllReportCache(self::REPORT_NAME);
    }

    public function getPeriodosFiscales(): array
    {
        return $this->rememberReportCache(
            self::REPORT_NAME,
            'periodos',
            [],
            function () {
                return $this->periodoFiscalService->getPeriodosFiscales()['periodosFiscales']
                    ->mapWithKeys(function ($periodo) {
                        $anio = $periodo->per_liano;
                        $mes = sprintf('%02d', $periodo->per_limes);
                        $periodoFiscal = $anio . $mes;

                        return [
                            $periodoFiscal => "Período " . $anio . " " . $this->getNombreMes($periodo->per_limes)
                        ];
                    })
                    ->toArray();
            }
        );
    }

    private function getNombreMes(int $mes): string
    {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        return $meses[$mes] ?? '';
    }
}
