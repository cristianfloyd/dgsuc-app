<?php

namespace App\Services;

use App\Models\Dh01;
use Illuminate\Database\Eloquent\Builder;

class ConceptoListadoService
{
    public function getQueryForConcepto(int $codn_conce): Builder
    {
        return Dh01::query()
            ->join('mapuche.dh03', 'dh01.nro_legaj', '=', 'dh03.nro_legaj')
            ->join('mapuche.dh11', 'dh03.codc_categ', '=', 'dh11.codc_categ')
            ->join('mapuche.dh21', 'dh01.nro_legaj', '=', 'dh21.nro_legaj')
            ->join('mapuche.dh22', 'dh21.nro_liqui', '=', 'dh22.nro_liqui')
            ->select([
                'dh03.codc_uacad',
                'dh22.per_liano',
                'dh22.per_limes',
                'dh22.desc_liqui',
                'dh01.nro_legaj',
                'dh01.nro_cuil1',
                'dh01.nro_cuil',
                'dh01.nro_cuil2',
                'dh01.desc_appat',
                'dh01.desc_nombr',
                'dh03.coddependesemp',
                'dh11.codc_categ',
                'dh11.codigoescalafon',
                'dh11.desc_categ',
                'dh03.codc_agrup',
                'dh03.codc_carac',
                'dh21.codn_conce',
                'dh21.tipo_conce',
                'dh21.impp_conce'
            ])
            ->where('dh21.codn_conce', $codn_conce);
    }
}
