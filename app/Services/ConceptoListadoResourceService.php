<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Reportes\ConceptoListado;
use Illuminate\Database\Eloquent\Builder;

class ConceptoListadoResourceService
{

    /**
     * Obtiene la consulta de ConceptoListado filtrada según los parámetros especificados.
     *
     * @param array $filters Un array asociativo con los filtros a aplicar. Las claves válidas son:
     *                       - 'codn_conce': un valor o array de valores para filtrar por el código de concepto.
     *                       - 'periodo_fiscal': un valor para filtrar por el período fiscal.
     *                       - 'nro_liqui': un valor para filtrar por el número de liquidación.
     * @return Builder La consulta de ConceptoListado con los filtros aplicados.
     */
    public function getFilteredQuery(array $filters = []): Builder
    {
        // Si no hay filtros, retornamos query vacía
        if (empty($filters)) {
            return ConceptoListado::query();
        }

        // Solo construimos la query si hay filtros
        $query = ConceptoListado::query();
        // dd($query->toSql());
        return $this->applyFilters($query, $filters);
    }


    /**
     * Aplica los filtros especificados a la consulta de ConceptoListado.
     *
     * @param Builder $query La consulta base a la que se aplicarán los filtros.
     * @param array $filters Un array asociativo con los filtros a aplicar. Las claves válidas son:
     *                       - 'codn_conce': un valor o array de valores para filtrar por el código de concepto.
     *                       - 'periodo_fiscal': un valor para filtrar por el período fiscal.
     *                       - 'nro_liqui': un valor para filtrar por el número de liquidación.
     * @return Builder La consulta con los filtros aplicados.
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        // Validamos que al menos exista un filtro obligatorio
        if (empty($filters['codn_conce'])) {
            return ConceptoListado::query()->whereRaw('1 = 0');
        }


        $query = ConceptoListado::query()
        ->when(
            $filters['codn_conce'] ?? null,
            fn ($query, $concepto) => $query->whereIn('codn_conce', (array)$concepto)
        )
        ->when(
            $filters['nro_liqui'] ?? null,
            fn ($query, $liquidacion) => $query->where('nro_liqui', $liquidacion)
        );

        return $query;
    }

    /**
     * Actualiza la vista materializada 'concepto_listado' y limpia la caché asociada.
     * Esta función se utiliza para refrescar los datos de la vista materializada que almacena
     * información relacionada con los conceptos de listado.
     */
    public function refreshMaterializedView(): void
    {
        DB::statement('REFRESH MATERIALIZED VIEW concepto_listado');
        Cache::tags(['concepto_listado'])->flush();
    }
}
