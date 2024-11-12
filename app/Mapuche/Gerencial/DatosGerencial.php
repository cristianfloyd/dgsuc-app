<?php

namespace App\Mapuche\Gerencial;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DatosGerencial
{
    static function get_datos_gerencial_nuevo(string $tabla_liqui = 'dh21', string $where = 'TRUE'): ?Collection
    {
        $ok = Gerencial::generar_datos_gerencial_nuevo($tabla_liqui, $where);
        $fecha = Fechas::get_fecha_fin_periodo_corriente();
        $map_esquema = 'mapuche';

        if ($ok) {
            return DB::table('datos_base_dh21 as db')
                ->join($map_esquema . '.dh01 as dh01', 'dh01.nro_legaj', '=', 'db.nro_legaj')
                ->join($map_esquema . '.dh03 as dh03', function ($join) {
                    $join->on('dh03.nro_legaj', '=', 'db.nro_legaj')
                        ->on('dh03.nro_cargo', '=', 'db.nro_cargo');
                })
                ->leftJoin($map_esquema . '.dh11', 'dh11.codc_categ', '=', 'db.codc_categ')
                ->leftJoin($map_esquema . '.dh31', 'dh31.codc_dedic', '=', 'dh11.codc_dedic')
                ->leftJoin('importes_netos as netos', function ($join) {
                    $join->on('netos.nro_legaj', '=', 'db.nro_legaj')
                        ->on('netos.nro_cargo', '=', 'db.nro_cargo')
                        ->on('netos.codn_fuent', '=', 'db.codn_fuent')
                        ->on('netos.codn_imput', '=', 'db.codn_imput')
                        ->on('netos.nro_liqui', '=', 'db.nro_liqui')
                        ->on('netos.nro_inciso', '=', 'db.nro_inciso');
                })
                ->leftJoin('datos_antiguedad as da', function ($join) {
                    $join->on('da.nro_legaj', '=', 'db.nro_legaj')
                        ->on('da.nro_cargo', '=', 'db.nro_cargo')
                        ->on('da.nro_liqui', '=', 'db.nro_liqui')
                        ->on('da.codn_imput', '=', 'db.codn_imput')
                        ->on('da.codn_fuent', '=', 'db.codn_fuent');
                })
                ->leftJoin('datos_trabajados as dt', function ($join) {
                    $join->on('dt.nro_legaj', '=', 'db.nro_legaj')
                        ->on('dt.nro_cargo', '=', 'db.nro_cargo')
                        ->on('dt.nro_liqui', '=', 'db.nro_liqui');
                })
                ->leftJoin($map_esquema . '.dh01_nomelegido', 'dh01.nro_legaj', '=', 'dh01_nomelegido.nro_legaj')
                ->select([
                    'db.codn_fuent',
                    'db.codn_depen',
                    DB::raw("LPAD(db.tipo_ejercicio::VARCHAR, 1, '0') AS tipo_ejercicio"),
                    DB::raw("LPAD(db.codn_grupo_presup::VARCHAR, 4, '0') AS codn_grupo_presup"),
                    // ... rest of the SELECT fields
                    DB::raw("COALESCE(netos.rem_s_apor, 0.00) AS rem_s_apor")
                ])
                ->orderBy('db.nro_liqui')
                ->orderBy('db.codn_depen')
                ->orderBy('codn_subar')
                ->orderBy('codn_subsubar')
                ->orderBy('db.codn_fuent')
                ->orderBy('codn_progr')
                ->orderBy('codn_subpr')
                ->orderBy('codn_proye')
                ->orderBy('codn_activ')
                ->orderBy('codn_obra')
                ->orderBy('codn_final')
                ->orderBy('codn_funci')
                ->orderBy('db.tipo_escal')
                ->get();
        }

        return null;
    }
}
