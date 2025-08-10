<?php

namespace App\Traits\Mapuche;

trait Dh21hQueries
{
    /**
     * Scope para filtrar por número de legajo.
     */
    public function scopeByLegajo($query, int $legajo)
    {
        return $query->where('nro_legaj', $legajo);
    }

    /**
     * Scope para filtrar por tipo de concepto.
     */
    public function scopeByTipoConcepto($query, string $tipo)
    {
        return $query->where('tipo_conce', $tipo);
    }
}
