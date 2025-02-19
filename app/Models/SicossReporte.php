<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

class SicossReporte extends Model
{
    use MapucheConnectionTrait;
    protected $table = 'mapuche.dh21h';
    protected $primaryKey = 'nro_liqui';
    public $timestamps = true;

    public function scopeGetReporte($query, $anio, $mes)
    {
        return $query
            ->select(
                'dh21h.nro_liqui',
                'dh22.desc_liqui',
                DB::raw('SUM(CASE WHEN codn_conce IN (201,202,203,205,204) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) AS aportesijpdh21'),
                DB::raw('SUM(CASE WHEN codn_conce IN (247) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) AS aporteinssjpdh21'),
                DB::raw('SUM(CASE WHEN codn_conce IN (301,302,303,304,307) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) AS contribucionsijpdh21'),
                DB::raw('SUM(CASE WHEN codn_conce IN (347) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) AS contribucioninssjpdh21'),
                DB::raw('SUM(CASE WHEN tipo_conce = \'C\' AND nro_orimp != 0 THEN impp_conce ELSE 0 END)::NUMERIC(15,2) AS remunerativo'),
                DB::raw('SUM(CASE WHEN tipo_conce = \'S\' THEN impp_conce ELSE 0 END)::NUMERIC(15,2) AS no_remunerativo')
            )
            ->join('mapuche.dh01', 'dh21h.nro_legaj', '=', 'dh01.nro_legaj')
            ->join('mapuche.dh22', 'dh21h.nro_liqui', '=', 'dh22.nro_liqui')
            ->whereIn('dh21h.nro_liqui', function($query) use ($anio, $mes) {
                $query->select('nro_liqui')
                    ->from('mapuche.dh22')
                    ->where('sino_genimp', true)
                    ->where('per_liano', $anio)
                    ->where('per_limes', $mes);
            })
            ->groupBy('dh21h.nro_liqui', 'dh22.desc_liqui');
    }
}
