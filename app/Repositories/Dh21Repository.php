<?php

namespace App\Repositories;

use App\NroLiqui;
use App\Models\Dh21;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Contracts\Dh21RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class Dh21Repository implements Dh21RepositoryInterface
{
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
     * Devuelve una instancia de Illuminate\Database\Eloquent\Builder que se puede usar para consultar el modelo Dh21.
     *
     * @return Builder
     */
    public function query(): Builder
    {
        return Dh21::query();
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

    /**
     * Obtiene las horas y días trabajados para un legajo y cargo específico
     *
     * @param int $legajo
     * @param int $cargo
     * @return array{dias: int, horas: int}
     */
    public function getHorasYDias(int $legajo, int $cargo): array
    {
        return Dh21::query()
            ->where('nro_legaj', $legajo)
            ->where('nro_cargo', $cargo)
            ->where('codn_conce', -51)
            ->select([
                DB::raw('MAX(nov1_conce) as dias'),
                DB::raw('MAX(nov2_conce) as horas')
            ])
            ->first()
            ->toArray();
    }

    /**
     * Obtiene las liquidaciones con sus importes y descripciones
     *
     * @param array $conditions Condiciones adicionales para filtrar
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLiquidaciones(array $conditions = []): Collection
    {
        return Dh21::query()
            ->select('dh22.desc_liqui', 'dh21.codn_conce', 'dh21.impp_conce', 'dh21.desc_conce')
            ->with('dh22')
            ->when(!empty($conditions), function ($query) use ($conditions) {
                foreach ($conditions as $column => $value) {
                    $query->where($column, $value);
                }
            })
            ->when(empty($conditions), function ($query) {
                $query->where('nro_legaj', '=', 1);
            })
            ->get();
    }

}
