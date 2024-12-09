<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Traits\MapucheConnectionTrait;
use App\Models\Reportes\ConceptoListado;
use Illuminate\Database\Eloquent\Builder;

class ConceptoListadoResourceService
{
    use MapucheConnectionTrait;

    public function getCacheKey(array $filters): string
    {
        return 'concepto_listado:' . md5(serialize($filters));
    }

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
            return ConceptoListado::query()
                ->orderBy('codc_uacad')
                ->orderBy('nro_legaj');
        }

        $cacheKey = $this->getCacheKey($filters);


        // Solo construimos la query si hay filtros
        $query = ConceptoListado::query();

        // Aplicamos los filtros
        $query = $this->applyFilters($query, $filters);

        // Retornamos el Builder directamente
        return $query;
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

    // Método separado para obtener resultados cacheados
    private function getCachedResults(Builder $query, string $cacheKey)
    {
        return Cache::store('file')->remember(
            $cacheKey,
            now()->addHours(2),
            fn() => $query->get()
        );
    }

    /**
     * Actualiza la vista materializada 'concepto_listado' y limpia la caché asociada.
     * Esta función se utiliza para refrescar los datos de la vista materializada que almacena
     * información relacionada con los conceptos de listado.
     */
    public function refreshMaterializedView(): void
    {
        DB::connection($this->getConnectionName())->statement('REFRESH MATERIALIZED VIEW suc.concepto_listado');
        Cache::tags(['concepto_listado'])->flush();
    }

    public function refreshCache(): void
    {
        Redis::connection()->flushdb();
    }
}
