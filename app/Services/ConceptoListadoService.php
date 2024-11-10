<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use App\Models\Reportes\ConceptoListado;
use Illuminate\Database\Eloquent\Builder;

class ConceptoListadoService
{
    use MapucheConnectionTrait;

    public function getBaseQuery(): Builder
    {
        return ConceptoListado::query();
    }

    /**
    * Obtiene una consulta de Eloquent para uno o varios conceptos.
    * Asegura un registro único por legajo considerando su cargo activo.
    *
    * @param array|int|null $codn_conce Código(s) del concepto a buscar
    * @param int|null $nro_liqui Número de liquidación
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function getQueryForConcepto(array|int|null $codn_conce, ?int $nro_liqui = null): Builder
    {
        $connection = $this->getConnectionName();
        $defaultNroLiqui = 2;

        // Subconsulta para obtener un único cargo por legajo
        $legajoCargo = DB::connection($connection)
            ->table('mapuche.dh21')
            ->select([
                DB::raw('DISTINCT ON (dh21.nro_legaj) dh21.nro_legaj'),
                'dh03.coddependesemp',
                'dh03.codc_uacad',
                'dh03.nro_cargo'
            ])
            ->join('mapuche.dh03', function($join) {
                $join->on('dh21.nro_legaj', '=', 'dh03.nro_legaj')
                    ->where('dh03.chkstopliq', '=', 0);
            })
            ->when(!is_null($codn_conce), function ($query) use ($codn_conce) {
                return is_array($codn_conce)
                    ? $query->whereIn('dh21.codn_conce', $codn_conce)
                    : $query->where('dh21.codn_conce', $codn_conce);
            })
            ->where('dh21.nro_liqui', operator: $nro_liqui ?? $defaultNroLiqui)
            ->groupBy(['dh21.nro_legaj', 'dh03.coddependesemp', 'dh03.codc_uacad', 'dh03.nro_cargo']);

        // Consulta principal
        return ConceptoListado::on($connection)
            ->from('mapuche.dh21')
            ->joinSub($legajoCargo, 'lc', function($join) {
                $join->on('dh21.nro_legaj', '=', 'lc.nro_legaj');
            })
            ->join('mapuche.dh01', 'dh21.nro_legaj', '=', 'dh01.nro_legaj')
            ->join('mapuche.dh22', 'dh21.nro_liqui', '=', 'dh22.nro_liqui')
            ->select([
                DB::raw("CONCAT(dh21.nro_legaj,'-',lc.coddependesemp,'-',dh21.nro_liqui,'-',dh21.codn_conce) as id"),
                'dh21.nro_legaj',
                'lc.codc_uacad',
                DB::raw("CONCAT(dh22.per_liano, LPAD(CAST(dh22.per_limes AS TEXT), 2, '0')) as periodo_fiscal"),
                'dh22.nro_liqui',
                'dh22.desc_liqui',
                'dh01.desc_appat',
                'dh01.desc_nombr',
                DB::raw("concat(dh01.nro_cuil1, dh01.nro_cuil, dh01.nro_cuil2) AS cuil"),
                'lc.coddependesemp',
                'lc.nro_cargo as secuencia',
                'dh21.codn_conce',
                'dh21.tipo_conce',
                'dh21.impp_conce'
            ])
            ->when(!is_null($codn_conce), function ($query) use ($codn_conce) {
                return is_array($codn_conce)
                    ? $query->whereIn('dh21.codn_conce', $codn_conce)
                    : $query->where('dh21.codn_conce', $codn_conce);
            })
            ->where('dh21.nro_liqui', operator: $nro_liqui ?? $defaultNroLiqui)
            ->orderBy('lc.codc_uacad')
            ->orderBy('lc.coddependesemp');
    }

    public function getQueryForConceptoListado(int $codn_conce): Builder
    {
        return ConceptoListado::query();
    }



    public function getQueryForConceptoRaw(int $codn_conce): Builder
    {
        return ConceptoListado::query()
            ->select([
                'codc_uacad',
                'periodo_fiscal',
                'nro_liqui',
                'desc_liqui',
                'nro_legaj',
                'cuil',
                'apellido',
                'nombre',
                'oficina_pago',
                'codc_categ',
                'codigoescalafon',
                'secuencia',
                'categoria_completa AS cargo',
                'codn_conce',
                'tipo_conce',
                'impp_conce'
            ])
            ->where('codn_conce', $codn_conce)
            ->orderBy('codc_uacad')
            ->orderBy('oficina_pago');
    }
}
