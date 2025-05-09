<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

trait ReportCacheTrait
{
    /**
     * Obtiene la clave de caché para un reporte.
     *
     * @param string $report Nombre del reporte
     * @param string $type Tipo de datos (data|totals)
     * @param array $params Parámetros adicionales
     * @return string
     */
    protected function getCacheKey(string $report, string $type, array $params = []): string
    {
        $prefix = Config::get("cache.reports.{$report}.prefix", "{$report}_report_");
        $paramsKey = implode('_', $params);
        return "{$prefix}{$type}_{$paramsKey}";
    }

    /**
     * Obtiene el TTL para un tipo de caché de reporte.
     *
     * @param string $report Nombre del reporte
     * @param string $type Tipo de datos (data|totals)
     * @return int
     */
    protected function getCacheTTL(string $report, string $type): int
    {
        return Config::get(
            "cache.reports.{$report}.{$type}_ttl",
            Config::get('cache.ttl', 3600)
        );
    }

    /**
     * Almacena datos en caché con el TTL configurado.
     *
     * @param string $report Nombre del reporte
     * @param string $type Tipo de datos (data|totals)
     * @param array $params Parámetros adicionales
     * @param \Closure $callback Función que genera los datos
     * @param int|null $customTtl TTL personalizado en segundos (opcional)
     * @return mixed
     */
    protected function rememberReportCache(string $report, string $type, array $params, \Closure $callback, ?int $customTtl = null)
    {
        $key = $this->getCacheKey($report, $type, $params);
        $ttl = $customTtl ?? $this->getCacheTTL($report, $type);

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Invalida el caché de un reporte.
     *
     * @param string $report Nombre del reporte
     * @param string $type Tipo de datos (data|totals)
     * @param array $params Parámetros adicionales
     * @return bool
     */
    protected function forgetReportCache(string $report, string $type, array $params = []): bool
    {
        return Cache::forget($this->getCacheKey($report, $type, $params));
    }

    /**
     * Invalida todo el caché relacionado con un reporte.
     *
     * @param string $report Nombre del reporte
     * @return bool
     */
    protected function forgetAllReportCache(string $report): bool
    {
        $prefix = Config::get("cache.reports.{$report}.prefix", "{$report}_report_");
        return Cache::tags($prefix)->flush();
    }
}
