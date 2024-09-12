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
                DB::raw("CASE WHEN dh03.codc_carac IN ('PERM','PLEN','REGU') THEN 'PERM' ELSE 'CONT' END AS codc_carac"),
                'dh21.codn_progr',
                DB::raw('ROUND(SUM(CASE WHEN dh21.tipo_conce = \'C\' AND dh21.codn_conce NOT IN (121, 122, 124, 125) THEN impp_conce ELSE 0 END)::NUMERIC, 2) AS remunerativo'),
                DB::raw('ROUND(SUM(CASE WHEN dh21.tipo_conce = \'S\' AND dh21.codn_conce != 186 THEN impp_conce ELSE 0 END)::NUMERIC, 2) AS no_remunerativo'),
                DB::raw('ROUND(SUM(CASE WHEN dh21.tipo_conce = \'D\' THEN impp_conce ELSE 0 END)::NUMERIC, 2) AS descuentos'),
                DB::raw('ROUND(SUM(CASE WHEN dh21.tipo_conce = \'A\' THEN impp_conce ELSE 0 END)::NUMERIC, 2) AS aportes'),
                DB::raw('ROUND(SUM(CASE WHEN dh21.codn_conce = 173 THEN impp_conce ELSE 0 END)::NUMERIC, 2) AS estipendio'),
                DB::raw('ROUND(SUM(CASE WHEN dh21.codn_conce = 186 THEN impp_conce ELSE 0 END)::NUMERIC, 2) AS med_resid'),
                DB::raw('0::NUMERIC(10,2) AS productividad'),
                DB::raw('0::NUMERIC(10,2) AS sal_fam'),
                DB::raw('ROUND(SUM(CASE WHEN dh21.codn_conce IN (121, 122, 124, 125) THEN impp_conce ELSE 0 END)::NUMERIC, 2) AS hs_extras'),
                DB::raw('ROUND((
                    SUM(CASE WHEN dh21.tipo_conce = \'C\' AND dh21.codn_conce NOT IN (121, 122, 124, 125) THEN impp_conce ELSE 0 END) +
                    SUM(CASE WHEN dh21.tipo_conce = \'S\' AND dh21.codn_conce != 186 THEN impp_conce ELSE 0 END) -
                    SUM(CASE WHEN dh21.tipo_conce = \'D\' THEN impp_conce ELSE 0 END) +
                    SUM(CASE WHEN dh21.codn_conce = 173 THEN impp_conce ELSE 0 END) +
                    SUM(CASE WHEN dh21.codn_conce = 186 THEN impp_conce ELSE 0 END) +
                    0 +
                    0 +
                    SUM(CASE WHEN dh21.codn_conce IN (121, 122, 124, 125) THEN impp_conce ELSE 0 END)
                )::NUMERIC, 2) AS total')
            ])
            ->selectRaw('SUM(CASE WHEN dh21.tipo_conce = \'C\' THEN impp_conce ELSE 0 END) AS remunerativo')
            ->selectRaw('SUM(CASE WHEN dh21.tipo_conce = \'S\' THEN impp_conce ELSE 0 END) AS no_remunerativo')
            ->selectRaw('SUM(CASE WHEN dh21.codn_conce IN (121, 124) THEN impp_conce ELSE 0 END) AS hs_extras')
            ->groupBy('banco', 'dh21.codn_funci', 'dh21.codn_fuent', 'dh21.codc_uacad', 'codc_carac', 'dh21.codn_progr')
            ->orderBy('banco', 'desc') // Agregar orderBy para el orden deseado
            ->orderBy('codn_funci')
            ->orderBy('codn_fuent')
            ->orderBy('codc_uacad')
            ->orderBy('codc_carac')
            ->orderBy('dh21.codn_progr')
            ->get();
    }
}
