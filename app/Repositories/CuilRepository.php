<?php

namespace App\Repositories;

use App\Contracts\CuilRepositoryInterface;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CuilRepository implements CuilRepositoryInterface
{
    use MapucheConnectionTrait;

    /**
     * Recupera los CUILs que están en afip_mapuche_sicoss pero no en afip_relaciones_activas
     * para un período fiscal específico.
     *
     * @param string $periodoFiscal Período fiscal en formato YYYYMM
     *
     * @return Collection Colección de CUILs
     */
    public function getCuilsNotInAfip(string $periodoFiscal): Collection
    {
        return DB::connection($this->getConnectionName())
            ->table('suc.afip_mapuche_sicoss as ams')
            ->select('ams.cuil')
            ->leftJoin('suc.afip_relaciones_activas as ara', function ($join) use ($periodoFiscal): void {
                $join->on('ams.cuil', '=', 'ara.cuil')
                    ->where('ara.periodo_fiscal', '=', $periodoFiscal);
            })
            ->whereNull('ara.cuil')
            ->where('ams.periodo_fiscal', '=', $periodoFiscal)
            ->pluck('ams.cuil');
    }

    /** Recupera las CUIL (Clave Única de Identificación Laboral) que están presentes en la tabla temporal tabla_temp_cuils pero no en la tabla afip_mapuche_mi_simplificacion.
     *
     * @return array The array of CUILs that are present in the temporary table but not in the afip_mapuche_mi_simplificacion table.
     */
    public function getCuilsNoEncontrados(): array
    {
        return DB::connection($this->getConnectionName())
            ->table('suc.tabla_temp_cuils as ttc')
            ->leftJoin('suc.afip_mapuche_mi_simplificacion as amms', 'ttc.cuil', 'amms.cuil')
            ->whereNull('amms.cuil')
            ->pluck('ttc.cuil')
            ->toArray();
    }
}
