<?php /** @noinspection PhpUnused */

/** @noinspection SqlResolve */

namespace App\Mapuche\Gerencial;

class Gerencial
{

    static function generar_datos_gerencial_nuevo($tabla_liqui, $where = 'TRUE'): bool
    {
        $map_esquema = 'mapuche';
        //-- Elimino las temporales que utilizo por si existen
        $sql = "DROP TABLE IF EXISTS suc.rep_ger_datos_base_dh21;
				DROP TABLE IF EXISTS suc.rep_ger_importes_netos;
				DROP TABLE IF EXISTS suc.rep_ger_datos_antiguedad;
				DROP TABLE IF EXISTS suc.rep_ger_datos_trabajados;";

        //-- Obtengo los datos basicos de dh21 sobre los cuales voy a
        //-- construir lo necesario para armar DH49.dbf y .xls
        $sql .= "SELECT DISTINCT
					dh21.codn_fuent,
					dh21.tipo_ejercicio,
					dh21.codn_grupo_presup,
					dh21.codn_area AS codn_depen,
					dh21.codn_subar,
					dh21.codn_subsubar,
					dh21.codn_progr,
					dh21.codn_subpr,
					dh21.codn_proye,
					dh21.codn_activ,
					dh21.codn_obra,
					dh21.codn_final,
					dh21.codn_funci,
					(LPAD(dh21.tipo_ejercicio::VARCHAR, 1, '0') || LPAD(dh21.codn_grupo_presup::VARCHAR, 4, '0') ||
					 LPAD(dh21.codn_area::VARCHAR, 3, '0') || LPAD(dh21.codn_subar::VARCHAR, 3, '0') || LPAD(dh21.codn_subsubar::VARCHAR, 3, '0') ||
					 LPAD(dh21.codn_progr::VARCHAR, 2, '0') || LPAD(dh21.codn_subpr::VARCHAR, 2, '0') || LPAD(dh21.codn_proye::VARCHAR, 2, '0') || LPAD(dh21.codn_activ::VARCHAR, 2, '0') || LPAD(dh21.codn_obra::VARCHAR, 2, '0') ||
				     LPAD(dh21.codn_final::VARCHAR, 2, '0') || LPAD(dh21.codn_funci::VARCHAR, 2, '0'))::VARCHAR(28) AS codn_imput,
					COALESCE(CASE WHEN dh35.tipo_carac = 'T' THEN
					CASE WHEN ((substr(dh17.objt_gtote,1,1)::int < 1) OR (substr(dh17.objt_gtote,1,1)::int > 5)) THEN 1
					ELSE substr(dh17.objt_gtote,1,1)::int END
					ELSE
					CASE WHEN ((substr(dh17.objt_gtope,1,1)::int < 1) OR (substr(dh17.objt_gtope,1,1)::int > 5)) THEN 1
					ELSE substr(dh17.objt_gtope,1,1)::int END
					END, 1) AS nro_inciso,
					dh21.nro_legaj,
					dh21.nro_cargo,
					dh03.codc_categ,
					dh03.coddependesemp,
					dh03.porc_aplic,
					dh03.cod_clasif_cargo,
					dh35.tipo_carac,
					CASE WHEN cuenta.nrolegajo IS NULL THEN 'N'
					ELSE 'S' END as en_banco,
					CASE WHEN dh21.tipoescalafon = 'C'
					THEN 'S' ELSE dh21.tipoescalafon END AS tipo_escal,
					dh21.codc_regio,
					dh03.codc_carac,
					dh03.fec_alta AS fecha_alta,
					dh03.fec_baja AS fecha_baja,
					COALESCE(dh24.porc_ipres, 0.00) AS porc_imput,
					dh22.per_liano AS ano_liqui,
					dh22.per_limes AS mes_liqui,
					dh22.nro_liqui,
					dh21.codigoescalafon,
					dh21.codc_uacad,
					dh03.codc_agrup,
					dh03.hs_dedic
					INTO TEMP TABLE suc.rep_ger_datos_base_dh21
				FROM
					" . $map_esquema . ".$tabla_liqui dh21
					JOIN " . $map_esquema . ".dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
					LEFT OUTER JOIN " . $map_esquema . ".dh01 ON (dh01.nro_legaj = dh21.nro_legaj)
					LEFT OUTER JOIN " . $map_esquema . ".dh17 ON (dh17.codn_conce = dh21.codn_conce)
					LEFT OUTER JOIN " . $map_esquema . ".dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
					LEFT OUTER JOIN " . $map_esquema . ".dh11 ON (dh03.codc_categ = dh11.codc_categ)
					LEFT OUTER JOIN " . $map_esquema . ".dh31 ON (dh11.codc_dedic = dh31.codc_dedic)
					LEFT OUTER JOIN " . $map_esquema . ".dh35 ON ((dh35.tipo_escal = dh21.tipoescalafon OR ( dh21.tipoescalafon = 'C' AND dh35.tipo_escal = 'S' )) AND dh35.codc_carac = dh03.codc_carac)
					LEFT OUTER JOIN " . $map_esquema . ".dh24 ON(dh24.nro_cargo  = dh21.nro_cargo AND
																dh24.codn_progr = dh21.codn_progr AND
																dh24.codn_subpr = dh21.codn_subpr AND
																dh24.codn_proye = dh21.codn_proye AND
																dh24.codn_activ = dh21.codn_activ AND
																dh24.codn_obra  = dh21.codn_obra AND
																dh24.codn_area  = dh21.codn_area AND
																dh24.codn_subar = dh21.codn_subar AND
																dh24.codn_subsubar = dh21.codn_subsubar AND
																dh24.codn_final = dh21.codn_final AND
																dh24.codn_funci = dh21.codn_funci AND
																dh24.tipo_ejercicio = dh21.tipo_ejercicio AND
																dh24.codn_grupo_presup = dh21.codn_grupo_presup AND
																dh24.codn_fuent = dh21.codn_fuent)
					LEFT JOIN (SELECT DISTINCT nrolegajo FROM " . $map_esquema . ".dh92) cuenta ON (dh03.nro_legaj = cuenta.nrolegajo)
				WHERE
					$where
				ORDER BY
					dh21.nro_legaj, dh21.nro_cargo, dh21.codn_fuent, codn_imput, dh22.nro_liqui, nro_inciso;";

        //-- Calculo los netos para el tipo de concepto C
        $sql .= "SELECT
					dh21.codn_fuent,
					(LPAD(dh21.tipo_ejercicio::VARCHAR, 1, '0') || LPAD(dh21.codn_grupo_presup::VARCHAR, 4, '0') ||
					 LPAD(dh21.codn_area::VARCHAR, 3, '0') || LPAD(dh21.codn_subar::VARCHAR, 3, '0') || LPAD(dh21.codn_subsubar::VARCHAR, 3, '0') ||
					 LPAD(dh21.codn_progr::VARCHAR, 2, '0') || LPAD(dh21.codn_subpr::VARCHAR, 2, '0') || LPAD(dh21.codn_proye::VARCHAR, 2, '0') || LPAD(dh21.codn_activ::VARCHAR, 2, '0') || LPAD(dh21.codn_obra::VARCHAR, 2, '0') ||
				     LPAD(dh21.codn_final::VARCHAR, 2, '0') || LPAD(dh21.codn_funci::VARCHAR, 2, '0'))::varchar(28) AS codn_imput,
					dh21.nro_legaj,
					dh21.nro_cargo,
					dh21.nro_liqui,
					COALESCE(CASE WHEN dh35.tipo_carac = 'T' THEN
					CASE WHEN ((substr(dh17.objt_gtote,1,1)::int < 1) OR (substr(dh17.objt_gtote,1,1)::int > 5)) THEN 1
					ELSE substr(dh17.objt_gtote,1,1)::int END
					ELSE
					CASE WHEN ((substr(dh17.objt_gtope,1,1)::int < 1) OR (substr(dh17.objt_gtope,1,1)::int > 5)) THEN 1
					ELSE substr(dh17.objt_gtope,1,1)::int END
					END, 1) AS nro_inciso,
					SUM(dh21.impp_conce)::numeric(10,2) AS netos_c
					INTO TEMP TABLE importes_netos_c
				FROM
					" . $map_esquema . ".$tabla_liqui dh21
					JOIN " . $map_esquema . ".dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
					LEFT OUTER JOIN " . $map_esquema . ".dh01 ON (dh01.nro_legaj = dh21.nro_legaj)
					LEFT OUTER JOIN " . $map_esquema . ".dh17 ON (dh17.codn_conce = dh21.codn_conce)
					LEFT OUTER JOIN " . $map_esquema . ".dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
					LEFT OUTER JOIN " . $map_esquema . ".dh11 ON (dh03.codc_categ = dh11.codc_categ)
					LEFT OUTER JOIN " . $map_esquema . ".dh31 ON (dh11.codc_dedic = dh31.codc_dedic)
					LEFT OUTER JOIN " . $map_esquema . ".dh35 ON (dh35.tipo_escal = dh21.tipoescalafon AND dh35.codc_carac = dh03.codc_carac)
				WHERE
					$where
					AND dh21.tipo_conce = 'C'
				GROUP BY
					dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui, nro_inciso
				ORDER BY
					dh21.nro_legaj, dh21.nro_cargo, dh21.codn_fuent, codn_imput, dh21.nro_liqui, nro_inciso;";

        //-- Calculo los netos para el tipo de concepto S
        $sql .= "SELECT
					dh21.codn_fuent,
					(LPAD(dh21.tipo_ejercicio::VARCHAR, 1, '0') || LPAD(dh21.codn_grupo_presup::VARCHAR, 4, '0') ||
					 LPAD(dh21.codn_area::VARCHAR, 3, '0') || LPAD(dh21.codn_subar::VARCHAR, 3, '0') || LPAD(dh21.codn_subsubar::VARCHAR, 3, '0') ||
					 LPAD(dh21.codn_progr::VARCHAR, 2, '0') || LPAD(dh21.codn_subpr::VARCHAR, 2, '0') || LPAD(dh21.codn_proye::VARCHAR, 2, '0') || LPAD(dh21.codn_activ::VARCHAR, 2, '0') || LPAD(dh21.codn_obra::VARCHAR, 2, '0') ||
				     LPAD(dh21.codn_final::VARCHAR, 2, '0') || LPAD(dh21.codn_funci::VARCHAR, 2, '0'))::varchar(28) AS codn_imput,
					dh21.nro_legaj,
					dh21.nro_cargo,
					dh21.nro_liqui,
					COALESCE(CASE WHEN dh35.tipo_carac = 'T' THEN
					CASE WHEN ((substr(dh17.objt_gtote,1,1)::int < 1) OR (substr(dh17.objt_gtote,1,1)::int > 5)) THEN 1
					ELSE substr(dh17.objt_gtote,1,1)::int END
					ELSE
					CASE WHEN ((substr(dh17.objt_gtope,1,1)::int < 1) OR (substr(dh17.objt_gtope,1,1)::int > 5)) THEN 1
					ELSE substr(dh17.objt_gtope,1,1)::int END
					END, 1) AS nro_inciso,
					SUM(dh21.impp_conce)::numeric(10,2) AS netos_s
					INTO TEMP TABLE importes_netos_s
				FROM
					" . $map_esquema . ".$tabla_liqui dh21
					JOIN " . $map_esquema . ".dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
					LEFT OUTER JOIN " . $map_esquema . ".dh01 ON (dh01.nro_legaj = dh21.nro_legaj)
					LEFT OUTER JOIN " . $map_esquema . ".dh17 ON (dh17.codn_conce = dh21.codn_conce)
					LEFT OUTER JOIN " . $map_esquema . ".dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
					LEFT OUTER JOIN " . $map_esquema . ".dh11 ON (dh03.codc_categ = dh11.codc_categ)
					LEFT OUTER JOIN " . $map_esquema . ".dh31 ON (dh11.codc_dedic = dh31.codc_dedic)
					LEFT OUTER JOIN " . $map_esquema . ".dh35 ON (dh35.tipo_escal = dh21.tipoescalafon AND dh35.codc_carac = dh03.codc_carac)
				WHERE
					$where
					AND dh21.tipo_conce = 'S'
				GROUP BY
					dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui, nro_inciso
				ORDER BY
					dh21.nro_legaj, dh21.nro_cargo, dh21.codn_fuent, codn_imput, dh21.nro_liqui, nro_inciso;";

        //-- Calculo los netos para el tipo de concepto O
        $sql .= "SELECT
					dh21.codn_fuent,
					(LPAD(dh21.tipo_ejercicio::VARCHAR, 1, '0') || LPAD(dh21.codn_grupo_presup::VARCHAR, 4, '0') ||
					 LPAD(dh21.codn_area::VARCHAR, 3, '0') || LPAD(dh21.codn_subar::VARCHAR, 3, '0') ||  LPAD(dh21.codn_subsubar::VARCHAR, 3, '0') ||
					 LPAD(dh21.codn_progr::VARCHAR, 2, '0')|| LPAD(dh21.codn_subpr::VARCHAR, 2, '0') || LPAD(dh21.codn_proye::VARCHAR, 2, '0') || LPAD(dh21.codn_activ::VARCHAR, 2, '0')|| LPAD(dh21.codn_obra::VARCHAR, 2, '0') ||
				     LPAD(dh21.codn_final::VARCHAR, 2, '0') || LPAD(dh21.codn_funci::VARCHAR, 2, '0'))::varchar(28) AS codn_imput,
					dh21.nro_legaj,
					dh21.nro_cargo,
					dh21.nro_liqui,
					COALESCE(CASE WHEN dh35.tipo_carac = 'T' THEN
					CASE WHEN ((substr(dh17.objt_gtote,1,1)::int < 1) OR (substr(dh17.objt_gtote,1,1)::int > 5)) THEN 1
					ELSE substr(dh17.objt_gtote,1,1)::int END
					ELSE
					CASE WHEN ((substr(dh17.objt_gtope,1,1)::int < 1) OR (substr(dh17.objt_gtope,1,1)::int > 5)) THEN 1
					ELSE substr(dh17.objt_gtope,1,1)::int END
					END, 1) AS nro_inciso,
					SUM(dh21.impp_conce)::numeric(10,2) AS netos_o
					INTO TEMP TABLE importes_netos_o
				FROM
					" . $map_esquema . ".$tabla_liqui dh21
					JOIN " . $map_esquema . ".dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
					LEFT OUTER JOIN " . $map_esquema . ".dh01 ON (dh01.nro_legaj = dh21.nro_legaj)
					LEFT OUTER JOIN " . $map_esquema . ".dh17 ON (dh17.codn_conce = dh21.codn_conce)
					LEFT OUTER JOIN " . $map_esquema . ".dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
					LEFT OUTER JOIN " . $map_esquema . ".dh11 ON (dh03.codc_categ = dh11.codc_categ)
					LEFT OUTER JOIN " . $map_esquema . ".dh31 ON (dh11.codc_dedic = dh31.codc_dedic)
					LEFT OUTER JOIN " . $map_esquema . ".dh35 ON (dh35.tipo_escal = dh21.tipoescalafon AND dh35.codc_carac = dh03.codc_carac)
				WHERE
					$where
					AND dh21.tipo_conce = 'O'
				GROUP BY
					dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui, nro_inciso
				ORDER BY
					dh21.nro_legaj, dh21.nro_cargo, dh21.codn_fuent, codn_imput, dh21.nro_liqui, nro_inciso;";

        //-- Calculo los netos para el tipo de concepto F
        $sql .= "SELECT
					dh21.codn_fuent,
					(LPAD(dh21.tipo_ejercicio::VARCHAR, 1, '0') || LPAD(dh21.codn_grupo_presup::VARCHAR, 4, '0') ||
					 LPAD(dh21.codn_area::VARCHAR, 3, '0') || LPAD(dh21.codn_subar::VARCHAR, 3, '0') || LPAD(dh21.codn_subsubar::VARCHAR, 3, '0') ||
					 LPAD(dh21.codn_progr::VARCHAR, 2, '0')|| LPAD(dh21.codn_subpr::VARCHAR, 2, '0') || LPAD(dh21.codn_proye::VARCHAR, 2, '0') || LPAD(dh21.codn_activ::VARCHAR, 2, '0') || LPAD(dh21.codn_obra::VARCHAR, 2, '0') ||
					 LPAD(dh21.codn_final::VARCHAR, 2, '0') || LPAD(dh21.codn_funci::VARCHAR, 2, '0'))::varchar(28) AS codn_imput,
					dh21.nro_legaj,
					dh21.nro_cargo,
					dh21.nro_liqui,
					COALESCE(CASE WHEN dh35.tipo_carac = 'T' THEN
					CASE WHEN ((substr(dh17.objt_gtote,1,1)::int < 1) OR (substr(dh17.objt_gtote,1,1)::int > 5)) THEN 1
					ELSE substr(dh17.objt_gtote,1,1)::int END
					ELSE
					CASE WHEN ((substr(dh17.objt_gtope,1,1)::int < 1) OR (substr(dh17.objt_gtope,1,1)::int > 5)) THEN 1
					ELSE substr(dh17.objt_gtope,1,1)::int END
					END, 1) AS nro_inciso,
					SUM(dh21.impp_conce)::numeric(10,2) AS netos_f
					INTO TEMP TABLE importes_netos_f
				FROM
					" . $map_esquema . ".$tabla_liqui dh21
					JOIN " . $map_esquema . ".dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
					LEFT OUTER JOIN " . $map_esquema . ".dh01 ON (dh01.nro_legaj = dh21.nro_legaj)
					LEFT OUTER JOIN " . $map_esquema . ".dh17 ON (dh17.codn_conce = dh21.codn_conce)
					LEFT OUTER JOIN " . $map_esquema . ".dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
					LEFT OUTER JOIN " . $map_esquema . ".dh11 ON (dh03.codc_categ = dh11.codc_categ)
					LEFT OUTER JOIN " . $map_esquema . ".dh31 ON (dh11.codc_dedic = dh31.codc_dedic)
					LEFT OUTER JOIN " . $map_esquema . ".dh35 ON (dh35.tipo_escal = dh21.tipoescalafon AND dh35.codc_carac = dh03.codc_carac)
				WHERE
					$where
					AND dh21.tipo_conce = 'F'
					GROUP BY
					dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui, nro_inciso
					ORDER BY
					dh21.nro_legaj, dh21.nro_cargo, dh21.codn_fuent, codn_imput, dh21.nro_liqui, nro_inciso;";

        //-- Calculo los netos para el tipo de concepto D
        $sql .= "SELECT
					dh21.codn_fuent,
					(LPAD(dh21.tipo_ejercicio::VARCHAR, 1, '0') || LPAD(dh21.codn_grupo_presup::VARCHAR, 4, '0') ||
					 LPAD(dh21.codn_area::VARCHAR, 3, '0') || LPAD(dh21.codn_subar::VARCHAR, 3, '0') || LPAD(dh21.codn_subsubar::VARCHAR, 3, '0') ||
					 LPAD(dh21.codn_progr::VARCHAR, 2, '0')|| LPAD(dh21.codn_subpr::VARCHAR, 2, '0') || LPAD(dh21.codn_proye::VARCHAR, 2, '0') || LPAD(dh21.codn_activ::VARCHAR, 2, '0') || LPAD(dh21.codn_obra::VARCHAR, 2, '0') ||
				     LPAD(dh21.codn_final::VARCHAR, 2, '0') || LPAD(dh21.codn_funci::VARCHAR, 2, '0'))::varchar(28) AS codn_imput,
					dh21.nro_legaj,
					dh21.nro_cargo,
					dh21.nro_liqui,
					COALESCE(CASE WHEN dh35.tipo_carac = 'T' THEN
					CASE WHEN ((substr(dh17.objt_gtote,1,1)::int < 1) OR (substr(dh17.objt_gtote,1,1)::int > 5)) THEN 1
					ELSE substr(dh17.objt_gtote,1,1)::int END
					ELSE
					CASE WHEN ((substr(dh17.objt_gtope,1,1)::int < 1) OR (substr(dh17.objt_gtope,1,1)::int > 5)) THEN 1
					ELSE substr(dh17.objt_gtope,1,1)::int END
					END, 1) AS nro_inciso,
					SUM(dh21.impp_conce)::numeric(10,2) AS netos_d
					INTO TEMP TABLE importes_netos_d
				FROM
					" . $map_esquema . ".$tabla_liqui dh21
					JOIN " . $map_esquema . ".dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
					LEFT OUTER JOIN " . $map_esquema . ".dh01 ON (dh01.nro_legaj = dh21.nro_legaj)
					LEFT OUTER JOIN " . $map_esquema . ".dh17 ON (dh17.codn_conce = dh21.codn_conce)
					LEFT OUTER JOIN " . $map_esquema . ".dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
					LEFT OUTER JOIN " . $map_esquema . ".dh11 ON (dh03.codc_categ = dh11.codc_categ)
					LEFT OUTER JOIN " . $map_esquema . ".dh31 ON (dh11.codc_dedic = dh31.codc_dedic)
					LEFT OUTER JOIN " . $map_esquema . ".dh35 ON (dh35.tipo_escal = dh21.tipoescalafon AND dh35.codc_carac = dh03.codc_carac)
				WHERE
					$where
					AND dh21.tipo_conce = 'D'
					GROUP BY
					dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui, nro_inciso
					ORDER BY
					dh21.nro_legaj, dh21.nro_cargo, dh21.codn_fuent, codn_imput, dh21.nro_liqui, nro_inciso;";

        //-- Calculo los netos para el tipo de concepto A
        $sql .= "SELECT
					dh21.codn_fuent,
					(LPAD(dh21.tipo_ejercicio::VARCHAR, 1, '0') || LPAD(dh21.codn_grupo_presup::VARCHAR, 4, '0') ||
					 LPAD(dh21.codn_area::VARCHAR, 3, '0') || LPAD(dh21.codn_subar::VARCHAR, 3, '0') || LPAD(dh21.codn_subsubar::VARCHAR, 3, '0') ||
					 LPAD(dh21.codn_progr::VARCHAR, 2, '0')|| LPAD(dh21.codn_subpr::VARCHAR, 2, '0') || LPAD(dh21.codn_proye::VARCHAR, 2, '0') || LPAD(dh21.codn_activ::VARCHAR, 2, '0') || LPAD(dh21.codn_obra::VARCHAR, 2, '0') ||
					 LPAD(dh21.codn_final::VARCHAR, 2, '0') || LPAD(dh21.codn_funci::VARCHAR, 2, '0'))::varchar(28) AS codn_imput,
					dh21.nro_legaj,
					dh21.nro_cargo,
					dh21.nro_liqui,
					COALESCE(CASE WHEN dh35.tipo_carac = 'T' THEN
					CASE WHEN ((substr(dh17.objt_gtote,1,1)::int < 1) OR (substr(dh17.objt_gtote,1,1)::int > 5)) THEN 1
					ELSE substr(dh17.objt_gtote,1,1)::int END
					ELSE
					CASE WHEN ((substr(dh17.objt_gtope,1,1)::int < 1) OR (substr(dh17.objt_gtope,1,1)::int > 5)) THEN 1
					ELSE substr(dh17.objt_gtope,1,1)::int END
					END, 1) AS nro_inciso,
					SUM(dh21.impp_conce)::numeric(10,2) AS netos_a
					INTO TEMP TABLE importes_netos_a
				FROM
					" . $map_esquema . ".$tabla_liqui dh21
					JOIN " . $map_esquema . ".dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
					LEFT OUTER JOIN " . $map_esquema . ".dh01 ON (dh01.nro_legaj = dh21.nro_legaj)
					LEFT OUTER JOIN " . $map_esquema . ".dh17 ON (dh17.codn_conce = dh21.codn_conce)
					LEFT OUTER JOIN " . $map_esquema . ".dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
					LEFT OUTER JOIN " . $map_esquema . ".dh11 ON (dh03.codc_categ = dh11.codc_categ)
					LEFT OUTER JOIN " . $map_esquema . ".dh31 ON (dh11.codc_dedic = dh31.codc_dedic)
					LEFT OUTER JOIN " . $map_esquema . ".dh35 ON (dh35.tipo_escal = dh21.tipoescalafon AND dh35.codc_carac = dh03.codc_carac)
				WHERE
					$where
					AND dh21.tipo_conce = 'A'
				GROUP BY
					dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui, nro_inciso
				ORDER BY
					dh21.nro_legaj, dh21.nro_cargo, dh21.codn_fuent, codn_imput, dh21.nro_liqui, nro_inciso;";

        //-- Armo los netos y los campos que se calculan en base a los netos
        $sql .= "SELECT
					db.nro_legaj,
					db.nro_cargo,
					db.codn_fuent,
					db.codn_imput,
					db.nro_liqui,
					db.nro_inciso,
					COALESCE(c.netos_c,0.00) + COALESCE(s.netos_s,0.00) + COALESCE(o.netos_o,0.00) + COALESCE(f.netos_f,0.00) + COALESCE(a.netos_a,0.00) AS imp_gasto,
					COALESCE(c.netos_c,0.00) + COALESCE(s.netos_s,0.00) + COALESCE(o.netos_o,0.00) + COALESCE(f.netos_f,0.00) AS imp_bruto,
					COALESCE(c.netos_c,0.00) + COALESCE(s.netos_s,0.00) + COALESCE(o.netos_o,0.00) + COALESCE(f.netos_f,0.00) - COALESCE(d.netos_d,0.00) AS imp_neto,
					COALESCE(d.netos_d, 0.00) AS imp_dctos,
					COALESCE(a.netos_a, 0.00) AS imp_aport,
					COALESCE(f.netos_f, 0.00) AS imp_familiar,
					COALESCE(c.netos_c, 0.00) AS rem_c_apor,
					COALESCE(o.netos_o, 0.00) AS otr_no_rem,
					COALESCE(s.netos_s, 0.00) AS rem_s_apor
					INTO TEMP TABLE suc.rep_ger_importes_netos
				FROM
					suc.rep_ger_datos_base_dh21 db
					LEFT JOIN importes_netos_a a on (a.nro_legaj = db.nro_legaj AND a.nro_cargo = db.nro_cargo AND a.codn_fuent = db.codn_fuent AND a.codn_imput = db.codn_imput AND a.nro_liqui = db.nro_liqui AND a.nro_inciso = db.nro_inciso)
					LEFT JOIN importes_netos_c c ON (c.nro_legaj = db.nro_legaj AND c.nro_cargo = db.nro_cargo AND c.codn_fuent = db.codn_fuent AND c.codn_imput = db.codn_imput AND c.nro_liqui = db.nro_liqui AND c.nro_inciso = db.nro_inciso)
					LEFT JOIN importes_netos_o o ON (o.nro_legaj = db.nro_legaj AND o.nro_cargo = db.nro_cargo AND o.codn_fuent = db.codn_fuent AND o.codn_imput = db.codn_imput AND o.nro_liqui = db.nro_liqui AND o.nro_inciso = db.nro_inciso)
					LEFT JOIN importes_netos_s s ON (s.nro_legaj = db.nro_legaj AND s.nro_cargo = db.nro_cargo AND s.codn_fuent = db.codn_fuent AND s.codn_imput = db.codn_imput AND s.nro_liqui = db.nro_liqui AND s.nro_inciso = db.nro_inciso)
					LEFT JOIN importes_netos_f f ON (f.nro_legaj = db.nro_legaj AND f.nro_cargo = db.nro_cargo AND f.codn_fuent = db.codn_fuent AND f.codn_imput = db.codn_imput AND f.nro_liqui = db.nro_liqui AND f.nro_inciso = db.nro_inciso)
					LEFT JOIN importes_netos_d d ON (d.nro_legaj = db.nro_legaj AND d.nro_cargo = db.nro_cargo AND d.codn_fuent = db.codn_fuent AND d.codn_imput = db.codn_imput AND d.nro_liqui = db.nro_liqui AND d.nro_inciso = db.nro_inciso)
				ORDER BY
					a.nro_legaj, a.nro_cargo, a.codn_fuent, a.codn_imput, a.nro_liqui, db.nro_inciso;";

        //-- Elimino las tablas temporales usadas para calculos intermedios
        $sql .= "DROP TABLE IF EXISTS importes_netos_c;
				DROP TABLE IF EXISTS importes_netos_s;
				DROP TABLE IF EXISTS importes_netos_o;
				DROP TABLE IF EXISTS importes_netos_f;
				DROP TABLE IF EXISTS importes_netos_d;
				DROP TABLE IF EXISTS importes_netos_a;";

        //-- Obtengo la antiguedad para cada cargo
        $sql .= "SELECT
					dh21.codn_fuent,
					(LPAD(dh21.tipo_ejercicio::VARCHAR, 1, '0') || LPAD(dh21.codn_grupo_presup::VARCHAR, 4, '0') ||
					 LPAD(dh21.codn_area::VARCHAR, 3, '0') || LPAD(dh21.codn_subar::VARCHAR, 3, '0') || LPAD(dh21.codn_subsubar::VARCHAR, 3, '0') ||
					 LPAD(dh21.codn_progr::VARCHAR, 2, '0') || LPAD(dh21.codn_subpr::VARCHAR, 2, '0') || LPAD(dh21.codn_proye::VARCHAR, 2, '0') || LPAD(dh21.codn_activ::VARCHAR, 2, '0') || LPAD(dh21.codn_obra::VARCHAR, 2, '0') ||
				     LPAD(dh21.codn_final::VARCHAR, 2, '0') || LPAD(dh21.codn_funci::VARCHAR, 2, '0'))::varchar(28) AS codn_imput,
					dh21.nro_legaj,
					dh21.nro_cargo,
					dh21.nro_liqui,
					MAX (dh21.nov1_conce) AS ano_antig,
					MAX (dh21.nov2_conce) AS mes_antig
					INTO TEMP TABLE suc.rep_ger_datos_antiguedad
				FROM
					" . $map_esquema . ".$tabla_liqui dh21
					JOIN " . $map_esquema . ".dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
					LEFT OUTER JOIN " . $map_esquema . ".dh01 ON (dh01.nro_legaj = dh21.nro_legaj)
					LEFT OUTER JOIN " . $map_esquema . ".dh17 ON (dh17.codn_conce = dh21.codn_conce)
					LEFT OUTER JOIN " . $map_esquema . ".dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
					LEFT OUTER JOIN " . $map_esquema . ".dh11 ON (dh03.codc_categ = dh11.codc_categ)
					LEFT OUTER JOIN " . $map_esquema . ".dh31 ON (dh11.codc_dedic = dh31.codc_dedic)
					LEFT OUTER JOIN " . $map_esquema . ".dh35 ON (dh35.tipo_escal = dh21.tipoescalafon AND dh35.codc_carac = dh03.codc_carac)
				WHERE
					$where
					AND dh21.codn_conce = (SELECT dato_parametro FROM " . $map_esquema . ".rrhhini WHERE nombre_seccion = 'Conceptos' AND nombre_parametro = 'Antiguedad')::int
					-- Controles nuevos para evitar que aparezcan las novedades cargadas...
					--Novedad_1 es menor que Novedad_2
					AND dh21.nov1_conce < dh21.nov2_conce
					--que Novedad_2 DIV 12 sea mas/menos novedad_1.
					AND (((trunc(dh21.nov2_conce/12)) = dh21.nov1_conce) OR ((trunc(dh21.nov2_conce/12)-1) = dh21.nov1_conce) OR ((trunc(dh21.nov2_conce/12)+ 1) = dh21.nov1_conce))
					--No puede ser mayor a 2 digitos novedad_1.
					AND (trunc(dh21.nov1_conce) < 100)
				GROUP BY
					dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui
				ORDER BY
					dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui;";

        //-- Obtengo las horas catedra y los días trabajados
        $where = str_replace('dh21.codn_conce > 0', 'dh21.codn_conce = -51 ', $where);
        $where = str_replace('AND dh21.nro_orimp > 0', ' ', $where);
        $sql .= "SELECT DISTINCT
					dh21.codn_fuent,
					(LPAD(dh21.tipo_ejercicio::VARCHAR, 1, '0') || LPAD(dh21.codn_grupo_presup::VARCHAR, 4, '0') ||
					 LPAD(dh21.codn_area::VARCHAR, 3, '0') || LPAD(dh21.codn_subar::VARCHAR, 3, '0') || LPAD(dh21.codn_subsubar::VARCHAR, 3, '0') ||
				     LPAD(dh21.codn_progr::VARCHAR, 2, '0')|| LPAD(dh21.codn_subpr::VARCHAR, 2, '0') || LPAD(dh21.codn_proye::VARCHAR, 2, '0') || LPAD(dh21.codn_activ::VARCHAR, 2, '0') || LPAD(dh21.codn_obra::VARCHAR, 2, '0') ||
					 LPAD(dh21.codn_final::VARCHAR, 2, '0') || LPAD(dh21.codn_funci::VARCHAR, 2, '0'))::varchar(28) AS codn_imput,
					dh21.nro_legaj,
					dh21.nro_cargo,
					dh21.nro_liqui,
					MAX (dh21.nov1_conce) AS dias_trab,
					MAX (dh21.nov2_conce) AS hs_catedra
					INTO TEMP TABLE datos_trabajados
				FROM
					" . $map_esquema . ".$tabla_liqui dh21
					JOIN " . $map_esquema . ".dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
					LEFT OUTER JOIN " . $map_esquema . ".dh01 ON (dh01.nro_legaj = dh21.nro_legaj)
					LEFT OUTER JOIN " . $map_esquema . ".dh17 ON (dh17.codn_conce = dh21.codn_conce)
					LEFT OUTER JOIN " . $map_esquema . ".dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
					LEFT OUTER JOIN " . $map_esquema . ".dh11 ON (dh03.codc_categ = dh11.codc_categ)
					LEFT OUTER JOIN " . $map_esquema . ".dh31 ON (dh11.codc_dedic = dh31.codc_dedic)
					LEFT OUTER JOIN " . $map_esquema . ".dh35 ON (dh35.tipo_escal = dh21.tipoescalafon AND dh35.codc_carac = dh03.codc_carac)
				WHERE
					$where
				GROUP BY
					dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui
				ORDER BY
					dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui;";

//        try {
//            toba::db()->ejecutar($sql);
//        } catch (toba_error_db $e) {
//            toba::logger()->error($e);
//            $mensaje = $e->getMessage();
//            toba::notificacion()->agregar($mensaje);
//            return false;
//        }
        return $sql;
    }

}
