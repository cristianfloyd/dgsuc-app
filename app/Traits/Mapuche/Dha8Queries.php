<?php

declare(strict_types=1);

namespace App\Traits\Mapuche;

trait Dha8Queries
{
    /**
     * Scope para filtrar por código de situación.
     */
    public function scopeBySituacion($query, int $codigosituacion)
    {
        return $query->where('codigosituacion', $codigosituacion);
    }

    /**
     * Scope para filtrar por zona.
     */
    public function scopeByZona($query, int $codigozona)
    {
        return $query->where('codigozona', $codigozona);
    }
}
