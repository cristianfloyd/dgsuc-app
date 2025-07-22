<?php

namespace App\Repositories\Decorators;

use App\Repositories\Interfaces\SicossReporteRepositoryInterface;
use App\Traits\ReportCacheTrait;
use Illuminate\Support\Collection; // Para logging si es necesario
use Illuminate\Support\Facades\Log; // Reutilizamos el trait para la lógica de caché

class CachingSicossReporteRepository implements SicossReporteRepositoryInterface
{
    use ReportCacheTrait;

    protected const string REPORT_NAME = 'sicoss_repo'; // Nombre específico para el caché del repo
    protected const int CACHE_TTL = 3600; // 1 hora, podría ser configurable

    protected SicossReporteRepositoryInterface $decoratedRepository;

    /**
     * Constructor del decorador de caché.
     *
     * @param SicossReporteRepositoryInterface $decoratedRepository El repositorio a decorar.
     */
    public function __construct(SicossReporteRepositoryInterface $decoratedRepository)
    {
        $this->decoratedRepository = $decoratedRepository;
    }

    /**
     * {@inheritdoc}
     * Obtiene los datos del reporte SICOSS, utilizando caché.
     */
    public function getReporte(string $anio, string $mes): Collection
    {
        $cacheKeyParams = [$anio, $mes];
        $cacheType = 'report_data';

        return $this->rememberReportCache(
            self::REPORT_NAME,
            $cacheType,
            $cacheKeyParams,
            fn () => $this->decoratedRepository->getReporte($anio, $mes),
            self::CACHE_TTL,
        );
    }

    /**
     * {@inheritdoc}
     * Obtiene los totales del reporte SICOSS, utilizando caché.
     */
    public function getTotales(string $anio, string $mes): array
    {
        $cacheKeyParams = [$anio, $mes];
        $cacheType = 'report_totals';

        return $this->rememberReportCache(
            self::REPORT_NAME,
            $cacheType,
            $cacheKeyParams,
            fn () => $this->decoratedRepository->getTotales($anio, $mes),
            self::CACHE_TTL,
        );
    }

    /**
     * {@inheritdoc}
     * Verifica si existen datos para un período fiscal específico, utilizando caché.
     */
    public function existenDatosParaPeriodo(string $anio, string $mes): bool
    {
        $cacheKeyParams = [$anio, $mes];
        $cacheType = 'period_exists';

        return $this->rememberReportCache(
            self::REPORT_NAME,
            $cacheType,
            $cacheKeyParams,
            fn () => $this->decoratedRepository->existenDatosParaPeriodo($anio, $mes),
            self::CACHE_TTL,
        );
    }

    /**
     * {@inheritdoc}
     * Obtiene los períodos fiscales disponibles, utilizando caché.
     */
    public function getPeriodosFiscalesDisponibles(): Collection
    {
        $cacheKeyParams = []; // No hay parámetros específicos para esta consulta global
        $cacheType = 'available_periods';

        // Un TTL más largo para datos que cambian con menos frecuencia
        $longTtl = self::CACHE_TTL * 24;

        return $this->rememberReportCache(
            self::REPORT_NAME,
            $cacheType,
            $cacheKeyParams,
            fn () => $this->decoratedRepository->getPeriodosFiscalesDisponibles(),
            $longTtl,
        );
    }

    /**
     * Invalida el caché para un reporte específico.
     * Podríamos añadir métodos para invalidar caché si es necesario.
     * Por ejemplo, invalidar el caché de un reporte específico.
     */
    public function invalidateReporteCache(string $anio, string $mes): void
    {
        $this->forgetReportCache(self::REPORT_NAME, 'report_data', [$anio, $mes]);
        Log::info("Caché invalidado para reporte SICOSS (datos): {$anio}-{$mes}");
    }

    /**
     * Invalida el caché para los totales de un reporte específico.
     */
    public function invalidateTotalesCache(string $anio, string $mes): void
    {
        $this->forgetReportCache(self::REPORT_NAME, 'report_totals', [$anio, $mes]);
        Log::info("Caché invalidado para reporte SICOSS (totales): {$anio}-{$mes}");
    }

    /**
     * Invalida el caché para la existencia de un período.
     */
    public function invalidatePeriodoExistsCache(string $anio, string $mes): void
    {
        $this->forgetReportCache(self::REPORT_NAME, 'period_exists', [$anio, $mes]);
        Log::info("Caché invalidado para reporte SICOSS (existencia): {$anio}-{$mes}");
    }

    /**
     * Invalida el caché de los períodos fiscales disponibles.
     */
    public function invalidatePeriodosFiscalesDisponiblesCache(): void
    {
        $this->forgetReportCache(self::REPORT_NAME, 'available_periods', []);
        Log::info('Caché invalidado para períodos fiscales disponibles SICOSS.');
    }

    /**
     * Invalida todo el caché gestionado por este decorador.
     */
    public function invalidateAllSicossRepoCache(): void
    {
        // El trait ReportCacheTrait tiene forgetAllReportCache que usa tags si están configurados,
        // o podríamos necesitar un método más específico si no usamos tags.
        // Por ahora, invalidaremos los tipos conocidos.
        // Esto es una simplificación; una estrategia de invalidación más robusta
        // podría usar tags de caché.
        $this->forgetAllReportCache(self::REPORT_NAME); // Asume que el trait maneja esto bien
        Log::info('Todo el caché del repositorio SICOSS ha sido invalidado.');
    }
}
