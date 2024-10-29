<?php

namespace App\Repositories;

use App\NroLiqui;
use App\Models\Dh21;
use Illuminate\Database\Eloquent\Builder;
use App\Contracts\Dh21RepositoryInterface;

class Dh21Repository implements Dh21RepositoryInterface
{
    /**
     * Devuelve una instancia de Illuminate\Database\Eloquent\Builder que se puede usar para consultar el modelo Dh21.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(): Builder
    {
        return Dh21::query();
    }

    /**
     * Devuelve la cantidad de legajos distintos en el modelo Dh21.
     *
     * @return int La cantidad de legajos distintos.
     */
    public function getDistinctLegajos(): int
    {
        return Dh21::query()->distinct('nro_legaj')->count();
    }

    /**
     * Devuelve la suma total del concepto 101 para un número de liquidación dado.
     *
     * @param NroLiqui|null $nroLiqui El número de liquidación para filtrar los registros. Si se omite, se devuelve la suma total de todos los registros.
     * @return float La suma total del concepto 101.
     */
    public function getTotalConcepto101(NroLiqui $nroLiqui = null): float
    {
        $query = Dh21::query()->where('codn_conce', '101');
        if ($nroLiqui) {
            $query->where('nro_liqui', $nroLiqui->getValue());
        }
        return $query->sum('impp_conce');
    }
}
