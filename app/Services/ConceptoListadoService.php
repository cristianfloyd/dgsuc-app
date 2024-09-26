<?php

namespace App\Services;

use App\Models\Dh01;
use Illuminate\Support\Facades\DB;
use App\Models\Reportes\ConceptoListado;
use Illuminate\Database\Eloquent\Builder;

class ConceptoListadoService
{
    /**
     * Obtiene una consulta de Eloquent para el concepto especificado.
     *
     * @param int $codn_conce El código del concepto a buscar.
     * @return \Illuminate\Database\Eloquent\Builder La consulta de Eloquent.
     */
    public function getQueryForConcepto(int $codn_conce): Builder
    {
        return Dh01::query()
            ->join('mapuche.dh03', 'dh01.nro_legaj', '=', 'dh03.nro_legaj')
            ->join('mapuche.dh11', 'dh03.codc_categ', '=', 'dh11.codc_categ')
            ->join('mapuche.dh21', 'dh01.nro_legaj', '=', 'dh21.nro_legaj')
            ->join('mapuche.dh22', 'dh21.nro_liqui', '=', 'dh22.nro_liqui')
            ->select([
                'dh03.codc_uacad',
                DB::raw("concat(dh22.per_liano, dh22.per_limes) AS periodo_fiscal"),
                'dh22.nro_liqui',
                'dh22.desc_liqui',
                'dh01.nro_legaj',
                DB::raw("concat(dh01.nro_cuil1, dh01.nro_cuil, dh01.nro_cuil2) AS cuil"),
                'dh01.desc_appat',
                'dh01.desc_nombr',
                'dh03.coddependesemp',
                'dh11.codigoescalafon',
                'dh03.nro_cargo',
                DB::raw("'secuencia' AS secuencia"),
                DB::raw("concat(dh11.desc_categ, dh03.codc_agrup, ' ', dh03.codc_carac) AS cargo"),
                'dh21.codn_conce',
                'dh21.tipo_conce',
                'dh21.impp_conce'
            ])
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
