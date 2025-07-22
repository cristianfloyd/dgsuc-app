<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface CargoFilterServiceInterface
{
    /**
     * Aplica un filtro de escalafón al consulta.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *                                                     La consulta a la que se aplicará el filtro.
     * @param string $codigoescalafon
     *                                El código del escalafón a filtrar.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     *                                               La consulta con el filtro aplicado.
     */
    public function aplicarFiltroEscalafon($query, $codigoescalafon): Builder;
}
