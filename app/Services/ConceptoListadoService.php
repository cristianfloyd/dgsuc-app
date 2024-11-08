<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Reportes\ConceptoListado;
use Illuminate\Database\Eloquent\Builder;

class ConceptoListadoService
{
    /**
     * Obtiene una consulta de Eloquent para el concepto especificado.
     *
     * @param int|array $codn_conce El código del concepto a buscar.
     * @return \Illuminate\Database\Eloquent\Builder La consulta de Eloquent.
     */
    public function getQueryForConcepto(int|array $codn_conce): Builder
    {
        return ConceptoListado::query()
            ->from('mapuche.dh21')
            ->join('mapuche.dh03', function($join) {
                $join->on('dh21.nro_legaj', '=', 'dh03.nro_legaj')
                    ->where('dh03.chkstopliq', '=', 0);
            })
            ->join('mapuche.dh11', 'dh03.codc_categ', '=', 'dh11.codc_categ')
            ->join('mapuche.dh01', 'dh21.nro_legaj', '=', 'dh01.nro_legaj')
            ->join('mapuche.dh22', 'dh21.nro_liqui', '=', 'dh22.nro_liqui')
            ->select([
                DB::raw("CONCAT(dh21.nro_legaj,'-',dh03.coddependesemp ,'-', dh21.nro_liqui, '-', dh21.codn_conce) as id"),
                'dh03.codc_uacad',
                DB::raw("CONCAT(dh22.per_liano, LPAD(CAST(dh22.per_limes AS TEXT), 2, '0')) as periodo_fiscal"),
                'dh22.nro_liqui',
                'dh22.desc_liqui',
                'dh21.nro_legaj',
                DB::raw("concat(dh01.nro_cuil1, dh01.nro_cuil, dh01.nro_cuil2) AS cuil"),
                'dh01.desc_appat',
                'dh01.desc_nombr',
                'dh03.coddependesemp',
                'dh11.codigoescalafon',
                'dh03.nro_cargo as secuencia',
                DB::raw("concat(dh11.desc_categ, dh03.codc_agrup, ' ', dh03.codc_carac) AS cargo"),
                'dh21.codn_conce',
                'dh21.tipo_conce',
                'dh21.impp_conce'
            ])
            ->where('dh21.codn_conce', $codn_conce)
            ->orderBy('codc_uacad')
            ->orderBy('coddependesemp')
            ;
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
