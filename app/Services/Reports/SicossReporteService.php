<?php

namespace App\Services\Reports;

use Exception;
use App\Traits\ReportCacheTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Data\Responses\SicossReporteData;
use App\Data\Responses\SicossTotalesData;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Repositories\Interfaces\SicossReporteRepositoryInterface;

class SicossReporteService
{
    use ReportCacheTrait;

    protected const REPORT_NAME = 'sicoss';
    protected const CACHE_TTL = 3600; // 1 hora en segundos

    protected PeriodoFiscalService $periodoFiscalService;
    protected SicossReporteRepositoryInterface $sicossReporteRepository;

    public function __construct(
        PeriodoFiscalService $periodoFiscalService,
        SicossReporteRepositoryInterface $sicossReporteRepository
    ) {
        $this->periodoFiscalService = $periodoFiscalService;
        $this->sicossReporteRepository = $sicossReporteRepository;
    }

    /**
     * Obtiene los datos del reporte SICOSS.
     *
     * @param string $anio
     * @param string $mes
     * @param bool $useCache Si debe utilizar caché o no
     * @return Collection<SicossReporteData>
     */
    public function getReporteData(string $anio, string $mes, bool $useCache = true): Collection
    {
        if (!$useCache) {
            return $this->sicossReporteRepository->getReporte($anio, $mes)
                ->map(fn($item) => SicossReporteData::fromModel($item));
        }

        return $this->rememberReportCache(
            self::REPORT_NAME,
            'data',
            [$anio, $mes],
            fn() => $this->sicossReporteRepository->getReporte($anio, $mes)
                ->map(fn($item) => SicossReporteData::fromModel($item)),
            self::CACHE_TTL
        );
    }

    /**
     * Obtiene los totales del reporte SICOSS.
     *
     * @param string $anio
     * @param string $mes
     * @param bool $useCache Si debe utilizar caché o no
     * @return SicossTotalesData
     */
    public function getTotales(string $anio, string $mes, bool $useCache = true): SicossTotalesData
    {
        try {
            $callback = function () use ($anio, $mes) {
                $totales = $this->sicossReporteRepository->getTotales($anio, $mes);
                return SicossTotalesData::fromArray($totales);
            };

            if (!$useCache) {
                return $callback();
            }

            return $this->rememberReportCache(
                self::REPORT_NAME,
                'totals',
                [$anio, $mes],
                $callback,
                self::CACHE_TTL
            );
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
     * Verifica si existen datos para un período fiscal específico.
     *
     * @param string $anio Año del período fiscal
     * @param string $mes Mes del período fiscal
     * @param bool $useCache Si debe utilizar caché o no (por defecto, true)
     * @return bool True si existen datos para el período, false en caso contrario
     */
    public function existenDatosParaPeriodo(string $anio, string $mes, bool $useCache = true): bool
    {
        // 1. Definicion de la arrow function como callback
        $callback = fn() => $this->sicossReporteRepository->existenDatosParaPeriodo($anio, $mes);

        // 2. Verificar si se debe utilizar caché
        if (!$useCache) {
            // 2.1 Si no se debe utilizar caché, simplemente ejecutar la función callback
            return $callback();
        }

        // 3. Si se usa caché, se intenta recuperar/guardar desde/en caché
        return $this->rememberReportCache(
            self::REPORT_NAME,
            'exist',
            [$anio, $mes],
            $callback,
            self::CACHE_TTL
        );
    }

    /**
     * Invalida el caché del reporte para un período específico.
     *
     * @param string $anio Año del período fiscal
     * @param string $mes Mes del período fiscal
     * @return void
     */
    public function invalidateCache(string $anio, string $mes): void
    {
        try {
            $this->forgetReportCache(self::REPORT_NAME, 'data', [$anio, $mes]);
            $this->forgetReportCache(self::REPORT_NAME, 'totals', [$anio, $mes]);
            $this->forgetReportCache(self::REPORT_NAME, 'exist', [$anio, $mes]);
        } catch (Exception $e) {
            Log::error('Error al invalidar caché del reporte SICOSS', [
                'error' => $e->getMessage(),
                'anio' => $anio,
                'mes' => $mes
            ]);
        }
    }

    /**
     * Invalida todo el caché del reporte SICOSS.
     *
     * @return void
     */
    public function invalidateAllCache(): void
    {
        try {
            $this->forgetAllReportCache(self::REPORT_NAME);
        } catch (Exception $e) {
            Log::error('Error al invalidar todo el caché del reporte SICOSS', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtiene los períodos fiscales disponibles.
     *
     * @param bool $useCache Si debe utilizar caché o no
     * @return array Períodos fiscales formateados
     */
    public function getPeriodosFiscales(bool $useCache = true): array
    {
        try {
            $callback = function () {
                $periodosFiscales = $this->periodoFiscalService->getPeriodosFiscales();

                // Verificar si la clave 'periodosFiscales' existe
                if (!isset($periodosFiscales['periodosFiscales'])) {
                    Log::warning('La clave periodosFiscales no existe en la respuesta', [
                        'respuesta' => $periodosFiscales
                    ]);
                    return [];
                }

                return $periodosFiscales['periodosFiscales']
                    ->mapWithKeys(function ($periodo) {
                        $anio = $periodo->per_liano;
                        $mes = sprintf('%02d', $periodo->per_limes);
                        $periodoFiscal = $anio . $mes;

                        return [
                            $periodoFiscal => "Período " . $anio . " " . $this->getNombreMes($periodo->per_limes)
                        ];
                    })
                    ->toArray();
            };

            if (!$useCache) {
                return $callback();
            }

            return $this->rememberReportCache(
                self::REPORT_NAME,
                'periodos',
                [],
                $callback,
                self::CACHE_TTL * 24 // 24 horas para los períodos fiscales
            );
        } catch (Exception $e) {
            Log::error('Error al obtener períodos fiscales', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [];
        }
    }

    /**
     * Obtiene períodos fiscales disponibles desde el repositorio.
     *
     * @param bool $useCache Si debe utilizar caché o no
     * @return array Períodos fiscales formateados
     */
    public function getPeriodosFiscalesFromRepo(bool $useCache = true): array
    {
        try {
            $callback = function () {
                return $this->sicossReporteRepository->getPeriodosFiscalesDisponibles()
                    ->mapWithKeys(function ($periodo) {
                        $anio = $periodo->per_liano;
                        $mes = sprintf('%02d', $periodo->per_limes);
                        $periodoFiscal = $anio . $mes;

                        return [
                            $periodoFiscal => "Período " . $anio . " " . $this->getNombreMes($periodo->per_limes)
                        ];
                    })
                    ->toArray();
            };

            if (!$useCache) {
                return $callback();
            }

            return $this->rememberReportCache(
                self::REPORT_NAME,
                'periodos_repo',
                [],
                $callback,
                self::CACHE_TTL * 24 // 24 horas para los períodos fiscales
            );
        } catch (Exception $e) {
            Log::error('Error al obtener períodos fiscales desde repositorio', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [];
        }
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
