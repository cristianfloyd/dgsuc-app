<?php

namespace App\Services\Mapuche;

use App\Models\Dh11;
use Illuminate\Database\Eloquent\Builder;
use App\Contracts\CargoFilterServiceInterface;

class CargoFilterService implements CargoFilterServiceInterface
{
    /**
     * Aplica un filtro de escalafón a una consulta.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query La consulta a la que se aplicará el filtro.
     * @param string $codigoescalafon El código del escalafón por el que se filtrará.
     * @return \Illuminate\Database\Eloquent\Builder La consulta con el filtro aplicado.
     */
    public function aplicarFiltroEscalafon($query, $codigoescalafon): Builder
    {
        if ($codigoescalafon !== 'TODOS') {
            $categorias = Dh11::getCategoriasPorTipo($codigoescalafon);
            return $query->whereIn('codc_categ', $categorias);
        }
        return $query;
    }
}
