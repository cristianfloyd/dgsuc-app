<?php

namespace App\Mapuche\Gerencial;

use Illuminate\Support\Facades\DB;

class DatosGerencial_old
{

    static function get_datos_gerencial_nuevo($tabla_liqui, $where = 'TRUE')
    {
        $ok = Gerencial::generar_datos_gerencial_nuevo($tabla_liqui, $where);

        $fecha = Fechas::get_fecha_fin_periodo_corriente();

        if ($ok) {
            //-- Armo la tabla definitiva
            $sql = "SELECT DISTINCT
						db.codn_fuent,
						db.codn_depen,
						LPAD(db.tipo_ejercicio::VARCHAR, 1, '0') AS tipo_ejercicio,
						LPAD(db.codn_grupo_presup::VARCHAR, 4, '0') AS codn_grupo_presup,
						LPAD(db.codn_depen::VARCHAR, 3, '0') AS codn_area,
						LPAD(db.codn_subar::VARCHAR, 3, '0') AS codn_subar,
						LPAD(db.codn_subsubar::VARCHAR, 3, '0') AS codn_subsubar,
						LPAD(db.codn_progr::VARCHAR, 2, '0') AS codn_progr,
						LPAD(db.codn_subpr::VARCHAR, 2, '0') AS codn_subpr,
						LPAD(db.codn_proye::VARCHAR, 2, '0') AS codn_proye,
						LPAD(db.codn_activ::VARCHAR, 2, '0') AS codn_activ,
						LPAD(db.codn_obra::VARCHAR, 2, '0') AS codn_obra,
						LPAD(db.codn_final::VARCHAR, 2, '0') AS codn_final,
						LPAD(db.codn_funci::VARCHAR, 2, '0') AS codn_funci,
						LPAD(db.codn_imput::VARCHAR, 28, '0') AS codn_imput,
						(LPAD(db.tipo_ejercicio::VARCHAR, 1, '0') ||'-'|| LPAD(db.codn_grupo_presup::VARCHAR, 4, '0') ||'-'||
					     LPAD(db.codn_depen::varchar,3, '0')||'.'||LPAD(db.codn_subar::varchar,3, '0')||'.'||LPAD(db.codn_subsubar::varchar,3, '0')||'-'||
						 LPAD(db.codn_fuent::varchar,2, '0')||'-'||
					     LPAD(db.codn_progr::varchar,2, '0')||'.'||LPAD(db.codn_subpr::varchar,2, '0')||'.'||LPAD(db.codn_proye::varchar,2, '0')||'.'||LPAD(db.codn_activ::varchar,2, '0')||'.'||LPAD(db.codn_obra::varchar,2, '0')||'-'||
					     LPAD(db.codn_final::varchar,2, '0')||'.'||LPAD(db.codn_funci::varchar,2, '0')) AS imputacion,
                        suc.geren_generar_imput_code(
                                dh21.tipo_ejercicio, dh21.codn_grupo_presup,
                                dh21.codn_area, dh21.codn_subar, dh21.codn_subsubar,
                                dh21.codn_progr, dh21.codn_subpr, dh21.codn_proye,
                                dh21.codn_activ, dh21.codn_obra, dh21.codn_final,
                                dh21.codn_funci
                        ) AS codn_imput,
						db.nro_inciso,
						db.nro_legaj,
						dh01.desc_appat || ', ' || dh01.desc_nombr AS desc_apyno,
						dh01_nomelegido,nombre_elegido,
						dh01_nomelegido,apellido_elegido,
						mapuche.map_get_edad('{$fecha}'::DATE, dh01.fec_nacim) AS cant_anios,
						COALESCE(da.ano_antig, 0.00) AS ano_antig,
						COALESCE(da.mes_antig, 0.00) AS mes_antig,
						db.nro_cargo,
						db.codc_categ,
						CASE WHEN db.codigoescalafon = 'NODO' THEN db.codc_agrup
						ELSE dh11.codc_dedic END AS codc_dedic,
						db.tipo_escal,
						db.codc_carac,
						db.codc_uacad,
						db.codc_regio,
						db.fecha_alta,
						db.fecha_baja,
						db.porc_imput,
						COALESCE(netos.imp_gasto, 0.00) AS imp_gasto,
						COALESCE(netos.imp_bruto, 0.00) AS imp_bruto,
						COALESCE(netos.imp_neto, 0.00) AS imp_neto,
						COALESCE(netos.imp_dctos, 0.00) AS imp_dctos,
						COALESCE(netos.imp_aport, 0.00) AS imp_aport,
						COALESCE(netos.imp_familiar, 0.00) AS imp_familiar,
						db.ano_liqui,
						db.mes_liqui,
						db.nro_liqui,
						dh01.tipo_estad,
						(dh01.nro_cuil1::VARCHAR || LPAD(dh01.nro_cuil::varchar, 8, '0') || dh01.nro_cuil2::VARCHAR) AS cuil,
						CASE WHEN (dh31.cant_horas = 0 AND dh31.tipo_horas = 'S') THEN dh03.hs_dedic
						ELSE dh31.cant_horas END AS hs_catedra,
						dt.dias_trab,
						COALESCE(netos.rem_c_apor, 0.00) AS rem_c_apor,
						COALESCE(netos.otr_no_rem, 0.00) AS otr_no_rem,
						db.en_banco,
						db.coddependesemp,
						db.porc_aplic,
						db.cod_clasif_cargo,
						db.tipo_carac,
						COALESCE(netos.rem_s_apor, 0.00) AS rem_s_apor
				FROM
					datos_base_dh21 db
					JOIN mapuche.dh01 dh01 ON (dh01.nro_legaj = db.nro_legaj)
					INNER JOIN mapuche.dh03 dh03 ON (dh03.nro_legaj = db.nro_legaj AND dh03.nro_cargo = db.nro_cargo)
					LEFT OUTER JOIN mapuche.dh11 ON (dh11.codc_categ = db.codc_categ)
					LEFT OUTER JOIN mapuche.dh31 ON (dh31.codc_dedic = dh11.codc_dedic)
					LEFT OUTER JOIN importes_netos netos ON (netos.nro_legaj = db.nro_legaj AND netos.nro_cargo = db.nro_cargo AND netos.codn_fuent = db.codn_fuent AND netos.codn_imput = db.codn_imput AND netos.nro_liqui = db.nro_liqui AND netos.nro_inciso = db.nro_inciso)
					LEFT OUTER JOIN datos_antiguedad da ON (da.nro_legaj = db.nro_legaj AND da.nro_cargo = db.nro_cargo AND da.nro_liqui = db.nro_liqui AND da.codn_imput = db.codn_imput AND da.codn_fuent = db.codn_fuent)
					LEFT OUTER JOIN datos_trabajados dt ON (dt.nro_legaj = db.nro_legaj AND dt.nro_cargo = db.nro_cargo AND dt.nro_liqui = db.nro_liqui)
					LEFT OUTER JOIN mapuche.dh01_nomelegido ON (dh01.nro_legaj = dh01_nomelegido.nro_legaj)
				ORDER BY
					db.nro_liqui, db.codn_depen, codn_subar, codn_subsubar, db.codn_fuent, codn_progr, codn_subpr, codn_proye, codn_activ, codn_obra, codn_final, codn_funci, db.tipo_escal;";

            return DB::select($sql);
//            return mapuche::consultar($sql);
        }
        return null;
    }
}
