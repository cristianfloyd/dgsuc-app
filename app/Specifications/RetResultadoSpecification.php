<?php

namespace App\Specifications;

use Illuminate\Database\Eloquent\Builder;
use App\ValueObjects\Periodo;
use App\ValueObjects\TipoRetro;

class RetResultadoSpecification
{
    /**
     * Aplica el criterio de búsqueda por periodo.
     *
     * @param Builder $query
     * @param Periodo $periodo
     * @return Builder
     */
    public function porPeriodo(Builder $query, Periodo $periodo): Builder
    {
        return $query->where('periodo', $periodo->getValue());
    }

    /**
     * Aplica el criterio de búsqueda por tipo de retro.
     *
     * @param Builder $query
     * @param TipoRetro $tipoRetro
     * @return Builder
     */
    public function porTipoRetro(Builder $query, TipoRetro $tipoRetro): Builder
    {
        return $query->where('tipo_retro', $tipoRetro->getValue());
    }

    /**
     * Aplica el criterio de búsqueda por rango de fechas.
     *
     * @param Builder $query
     * @param \DateTime $desde
     * @param \DateTime $hasta
     * @return Builder
     */
    public function porRangoFechas(Builder $query, \DateTime $desde, \DateTime $hasta): Builder
    {
        return $query->whereBetween('fecha_ret_desde', [$desde, $hasta]);
    }
}
