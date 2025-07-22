<?php

namespace App\Services\Reports;

// use App\Traits\ReportCacheTrait;
use App\Data\Responses\SicossReporteData;
use App\Data\Responses\SicossTotalesData;
use App\Repositories\Decorators\CachingSicossReporteRepository;
use App\Repositories\Interfaces\SicossReporteRepositoryInterface;
use App\Services\Mapuche\PeriodoFiscalService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SicossReporteService
{
    //use ReportCacheTrait;

    // protected const REPORT_NAME = 'sicoss';
    // protected const CACHE_TTL = 3600; // 1 hora en segundos

    protected PeriodoFiscalService $periodoFiscalService;

    protected SicossReporteRepositoryInterface $sicossReporteRepository;

    /**
     * Constructor del servicio.
     *
     * @param PeriodoFiscalService $periodoFiscalService Servicio de períodos fiscales.
     * @param SicossReporteRepositoryInterface $repository Repositorio de reportes SICOSS (posiblemente decorado).
     */
    public function __construct(
        PeriodoFiscalService $periodoFiscalService,
        SicossReporteRepositoryInterface $sicossReporteRepository,
    ) {
        $this->periodoFiscalService = $periodoFiscalService;
        $this->sicossReporteRepository = $sicossReporteRepository;
    }

    /**
     * Obtiene los datos del reporte SICOSS.
     *
     * @param string $anio
     * @param string $mes
     *
     * @return Collection<SicossReporteData>
     */
    public function getReporteData(string $anio, string $mes): Collection
    {
        try {
            return $this->sicossReporteRepository->getReporte($anio, $mes)
                ->map(fn ($item) => SicossReporteData::fromModel($item));
        } catch (\Throwable $th) {
            Log::error('Error al obtener datos del reporte SICOSS desde el servicio:', [
                'error' => $th->getMessage(),
                'anio' => $anio,
                'mes' => $mes,
                'trace' => $th->getTraceAsString(),
            ]);
            return collect();
        }
    }

    /**
     * Obtiene los totales del reporte SICOSS.
     *
     * @param string $anio
     * @param string $mes
     *
     * @return SicossTotalesData
     */
    public function getTotales(string $anio, string $mes): SicossTotalesData
    {
        try {
            $totales = $this->sicossReporteRepository->getTotales($anio, $mes);
            return SicossTotalesData::fromArray($totales);

        } catch (\Exception $e) {
            Log::error('Error al obtener totales del reporte SICOSS', [
                'error' => $e->getMessage(),
                'anio' => $anio,
                'mes' => $mes,
                'trace' => $e->getTraceAsString(),
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
     * El caché es manejado por el repositorio decorado.
     *
     * @param string $anio
     * @param string $mes
     *
     * @return bool
     */
    public function existenDatosParaPeriodo(string $anio, string $mes): bool
    {
        try {
            // El parámetro $useCache ya no es necesario aquí
            return $this->sicossReporteRepository->existenDatosParaPeriodo($anio, $mes);
        } catch (\Exception $e) {
            Log::error('Error al verificar existencia de datos para período desde el servicio', [
                'error' => $e->getMessage(),
                'anio' => $anio,
                'mes' => $mes,
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Invalida el caché del reporte para un período específico.
     * Delega al repositorio si este tiene métodos de invalidación.
     *
     * @param string $anio
     * @param string $mes
     *
     * @return void
     */
    public function invalidateCache(string $anio, string $mes): void
    {
        if ($this->sicossReporteRepository instanceof CachingSicossReporteRepository) {
            try {
                $this->sicossReporteRepository->invalidateReporteCache($anio, $mes);
                $this->sicossReporteRepository->invalidateTotalesCache($anio, $mes);
                $this->sicossReporteRepository->invalidatePeriodoExistsCache($anio, $mes);
            } catch (\Exception $e) {
                Log::error('Error al invalidar caché del reporte SICOSS a través del servicio', [
                    'error' => $e->getMessage(),
                    'anio' => $anio,
                    'mes' => $mes,
                ]);
            }
        } else {
            Log::warning('Se intentó invalidar caché, pero el repositorio no es una instancia de CachingSicossReporteRepository.');
        }
    }

    /**
     * Invalida todo el caché del reporte SICOSS.
     *
     * @return void
     */
    public function invalidateAllCache(): void
    {
        if ($this->sicossReporteRepository instanceof CachingSicossReporteRepository) {
            try {
                $this->sicossReporteRepository->invalidateAllSicossRepoCache();
            } catch (\Exception $e) {
                Log::error('Error al invalidar todo el caché del reporte SICOSS a través del servicio', [
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            Log::warning('Se intentó invalidar todo el caché, pero el repositorio no es una instancia de CachingSicossReporteRepository.');
        }
    }

    /**
     * Obtiene los períodos fiscales disponibles desde el PeriodoFiscalService.
     * Este método NO utiliza el repositorio SICOSS y su caché, sino el servicio de períodos fiscales.
     * Mantenemos el caché original para este método si es necesario.
     *
     * @return array Períodos fiscales formateados.
     */
    public function getPeriodosFiscales(): array
    {
        // Si este método debe tener su propio caché independiente del repositorio,
        // necesitaríamos reintroducir ReportCacheTrait o una lógica similar aquí.
        // Por ahora, lo dejamos sin caché explícito en el servicio, asumiendo que
        // $periodoFiscalService podría tener su propio manejo de caché.
        try {
            $periodosFiscalesRespuesta = $this->periodoFiscalService->getPeriodosFiscales();

            if (!isset($periodosFiscalesRespuesta['periodosFiscales']) || !$periodosFiscalesRespuesta['periodosFiscales'] instanceof Collection) {
                Log::warning('La clave periodosFiscales no existe o no es una colección en la respuesta de PeriodoFiscalService', [
                    'respuesta' => $periodosFiscalesRespuesta,
                ]);
                return [];
            }

            return $periodosFiscalesRespuesta['periodosFiscales']
                ->mapWithKeys(function ($periodo) {
                    $anio = $periodo->per_liano;
                    $mes = \sprintf('%02d', $periodo->per_limes);
                    $periodoFiscal = $anio . $mes;

                    return [
                        $periodoFiscal => 'Período ' . $anio . ' ' . $this->getNombreMes($periodo->per_limes),
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error al obtener períodos fiscales desde PeriodoFiscalService', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    /**
     * Obtiene los períodos fiscales disponibles.
     * El caché es manejado por el repositorio decorado.
     *
     * @return array
     */
    public function getPeriodosFiscalesFromRepo(): array
    {
        try {
            // El parámetro $useCache ya no es necesario aquí
            return $this->sicossReporteRepository->getPeriodosFiscalesDisponibles()
                ->mapWithKeys(function ($periodo) {
                    $anio = $periodo->per_liano;
                    $mes = \sprintf('%02d', $periodo->per_limes);
                    $periodoFiscal = $anio . $mes;

                    return [
                        $periodoFiscal => 'Período ' . $anio . ' ' . $this->getNombreMes($periodo->per_limes),
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error al obtener períodos fiscales desde repositorio (vía servicio)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    /**
     * Obtiene el nombre del mes a partir de su número.
     *
     * @param int $mes Número del mes (1-12).
     *
     * @return string Nombre del mes en español.
     */
    private function getNombreMes(int $mes): string
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        return $meses[$mes] ?? "Mes {$mes} inválido"; // Mejor manejo para mes inválido
    }
}
