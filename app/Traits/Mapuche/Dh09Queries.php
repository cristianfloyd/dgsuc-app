<?php

declare(strict_types=1);

namespace App\Traits\Mapuche;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Trait para consultas específicas del modelo Dh09.
 */
trait Dh09Queries
{
    /**
     * Scope para filtrar personal activo.
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->whereNull('fec_defun')
            ->whereNull('fecha_jubilacion');
    }

    /**
     * Scope para filtrar por unidad académica.
     */
    public function scopePorUnidadAcademica(Builder $query, string $codc_uacad): Builder
    {
        return $query->where('codc_uacad', $codc_uacad);
    }

    /**
     * Scope para filtrar por estado civil.
     */
    public function scopePorEstadoCivil(Builder $query, string $codc_estcv): Builder
    {
        return $query->where('codc_estcv', $codc_estcv);
    }

    /**
     * Scope para filtrar personal con embargos.
     */
    public function scopeConEmbargos(Builder $query): Builder
    {
        return $query->where('sino_embargo', true);
    }

    /**
     * Scope para filtrar por rango de fechas de ingreso.
     */
    public function scopePorRangoFechaIngreso(
        Builder $query,
        Carbon $desde,
        Carbon $hasta,
    ): Builder {
        return $query->whereBetween('fec_ingreso', [$desde, $hasta]);
    }

    /**
     * Scope para filtrar jubilados.
     */
    public function scopeJubilados(Builder $query): Builder
    {
        return $query->whereNotNull('fecha_jubilacion');
    }

    /**
     * Obtener personal por región.
     */
    public function scopePorRegion(Builder $query, string $codc_regio): Builder
    {
        return $query->where('codc_regio', $codc_regio);
    }

    /**
     * Scope para filtrar por obra social.
     */
    public function scopePorObraSocial(Builder $query, string $codc_obsoc): Builder
    {
        return $query->where('codc_obsoc', $codc_obsoc);
    }

    /**
     * Obtener personal con antigüedad mayor a X años.
     */
    public function scopeConAntiguedadMayorA(Builder $query, int $años): Builder
    {
        $fechaLimite = now()->subYears($años);
        return $query->where('fec_ingreso', '<=', $fechaLimite);
    }

    /**
     * Scope para filtrar por modalidad de contratación.
     */
    public function scopePorModalidadContratacion(Builder $query, int $codigomodalcontrat): Builder
    {
        return $query->where('codigomodalcontrat', $codigomodalcontrat);
    }

    /**
     * Obtener personal con asignaciones familiares.
     */
    public function scopeConAsignacionesFamiliares(Builder $query): Builder
    {
        return $query->whereNotNull('ua_asigfamiliar');
    }

    /**
     * Métodos de consulta compuestos.
     */
    public function getActivosPorUnidad(string $codc_uacad): Collection
    {
        return $this->activos()
            ->porUnidadAcademica($codc_uacad)
            ->get();
    }

    /**
     * Obtener personal próximo a jubilarse.
     */
    public function getProximosJubilarse(int $edadJubilacion = 65): Collection
    {
        $fechaLimite = now()->subYears($edadJubilacion - 1);

        return $this->activos()
            ->where('fec_altos', '<=', $fechaLimite)
            ->get();
    }

    /**
     * Obtener estadísticas por unidad académica.
     */
    public function getEstadisticasPorUnidad(string $codc_uacad): array
    {
        $query = $this->porUnidadAcademica($codc_uacad);

        return [
            'total' => $query->count(),
            'activos' => $query->clone()->activos()->count(),
            'jubilados' => $query->clone()->jubilados()->count(),
            'con_embargos' => $query->clone()->conEmbargos()->count(),
            'con_asignaciones' => $query->clone()->conAsignacionesFamiliares()->count(),
        ];
    }
}
