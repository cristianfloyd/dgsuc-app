<?php

namespace App\Traits\Mapuche;

trait Dh35Queries
{
    /**
     * Obtiene caracteres con control de planta activo
     */
    public function scopeConControlPlanta($query)
    {
        return $query->where('controlcargos', 1);
    }

    /**
     * Obtiene caracteres ordenados por número de orden
     */
    public function scopeOrdenadoPorOrden($query)
    {
        return $query->orderBy('tipo_escal')
                    ->orderBy('nro_orden');
    }
}
