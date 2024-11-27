<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait para consultas complejas del modelo Embargo
 */
trait EmbargoQueries
{
    /**
     * Scope para embargos activos
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->whereHas('estado', function($q) {
            $q->where('es_activo', true);
        });
    }

    /**
     * Scope para embargos por vencer
     */
    public function scopePorVencer(Builder $query, int $dias = 30): Builder
    {
        return $query->whereNotNull('fec_finalizacion')
            ->whereRaw('fec_finalizacion <= CURRENT_DATE + ?::interval', ["$dias days"]);
    }
}
