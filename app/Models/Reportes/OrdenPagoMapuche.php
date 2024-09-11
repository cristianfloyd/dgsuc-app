<?php

namespace App\Models\Reportes;

use App\Models\Dh21;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Traits\MapucheDesaConnectionTrait;
use Illuminate\Database\Eloquent\Model;

class OrdenPagoMapuche extends Model
{
    use MapucheDesaConnectionTrait;

    public function getOrdenPago(): Collection
    {
        return Dh21::query()
            ->join('dh22', 'dh21.nro_liqui', '=', 'dh22.nro_liqui')
            ->join('dh03', 'dh21.nro_cargo', '=', 'dh03.nro_cargo')
            ->join('dh12', 'dh21.codn_conce', '=', 'dh12.codn_conce')
            ->leftJoin('dh92', 'dh21.nro_legaj', '=', 'dh92.nrolegajo')
            ->where('dh22.nro_liqui', 1)
            ->select([
                DB::raw('CASE WHEN dh92.codn_banco IN (0,1) THEN 0 ELSE 1 END AS banco'),
                'dh21.codn_funci',
                'dh21.codn_fuent',
                'dh21.codc_uacad',
                // 'dh03.codc_carac',
                // 'dh22.nro_liqui',
                // 'dh22.desc_liqui',
                // 'dh21.tipoescalafon',
                'dh21.codn_progr',
                // 'dh21.codn_subpr',
                // 'dh21.codn_proye',
                // 'dh21.codn_activ',
                // 'dh21.codn_conce',
                DB::raw('0 as estipendio'),
                DB::raw('0 as productividad'),
                DB::raw('0 as med_resid'),
                DB::raw('0 as sal_fam'),
                DB::raw('(SUM(CASE WHEN dh21.tipo_conce = \'C\' THEN impp_conce ELSE 0 END) +
                    SUM(CASE WHEN dh21.tipo_conce = \'S\' THEN impp_conce ELSE 0 END) +
                    SUM(CASE WHEN dh21.codn_conce IN (121, 124) THEN impp_conce ELSE 0 END)) AS total'),
            ])
            ->selectRaw('SUM(CASE WHEN dh21.tipo_conce = \'C\' THEN impp_conce ELSE 0 END) AS remunerativo')
            ->selectRaw('SUM(CASE WHEN dh21.tipo_conce = \'S\' THEN impp_conce ELSE 0 END) AS no_remunerativo')
            ->selectRaw('SUM(CASE WHEN dh21.codn_conce IN (121, 124) THEN impp_conce ELSE 0 END) AS hs_extras')
            ->groupBy([
                'dh92.codn_banco',
                'dh21.codn_funci',
                'dh21.codn_fuent',
                'dh21.codc_uacad',
                'dh03.codc_carac',
                'dh21.codn_progr',
            ])
            ->orderBy('banco') // Agregar orderBy para el orden deseado
            ->orderBy('codn_funci')
            ->orderBy('codn_fuent')
            ->orderBy('codc_uacad')
            ->get();
    }
}
