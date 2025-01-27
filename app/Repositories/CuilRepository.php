<?php

namespace App\Repositories;

use App\Models\AfipMapucheSicoss;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use App\Contracts\CuilRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CuilRepository implements CuilRepositoryInterface
{
    use MapucheConnectionTrait;

    /**
     * Recupera las CUIL (Clave Única de Identificación Laboral) que están presentes en la tabla temporal tabla_temp_cuils pero no en la tabla afip_mapuche_mi_simplificacion.
     *
     * @param int $perPage Número de resultados por página (opcional, por defecto 10)
     *  Paginador de los CUIL que no se encuentran en la tabla afip_mapuche_mi_simplificacion
     *
     */
    public function getCuilsNotInAfip($perPage = 10): Collection
    {
        $cuils = AfipMapucheSicoss::on($this->getConnectionName())
        ->select('cuil')
        ->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('suc.afip_relaciones_activas')
                ->whereColumn('afip_relaciones_activas.cuil', 'afip_mapuche_sicoss.cuil');
        })
        ->pluck('cuil');
    // return $this->paginateResults($cuils, $perPage);
        return new Collection($cuils);
    }


    /** Recupera las CUIL (Clave Única de Identificación Laboral) que están presentes en la tabla temporal tabla_temp_cuils pero no en la tabla afip_mapuche_mi_simplificacion.
     *
     * @return array The array of CUILs that are present in the temporary table but not in the afip_mapuche_mi_simplificacion table.
     */
    public function getCuilsNoEncontrados(): array
    {
        $cuilsNoEncontrados = DB::connection($this->getConnectionName())
            ->table('suc.tabla_temp_cuils as ttc')
            ->leftJoin('suc.afip_mapuche_mi_simplificacion as amms', 'ttc.cuil', 'amms.cuil')
            ->whereNull('amms.cuil')
            ->pluck('ttc.cuil')
            ->toArray();

        return $cuilsNoEncontrados;
    }

    /**
     * Pagina los resultados de una colección.
     *
     * @param \Illuminate\Support\Collection $collection La colección a paginar.
     * @param int $perPage El número de resultados por página.
     * @return \Illuminate\Pagination\LengthAwarePaginator El paginador de los resultados.
     */
    private function paginateResults($collection, $perPage): LengthAwarePaginator
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $collection->slice(($currentPage - 1) * $perPage, $perPage)->all();
        return new LengthAwarePaginator(
            $currentPageItems,
            $collection->count(),
            $perPage
        );
    }
}
