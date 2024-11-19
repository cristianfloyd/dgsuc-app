$where = str_replace('dh21.codn_conce > 0', 'dh21.codn_conce = -51 ', $where);
$where = str_replace('AND dh21.nro_orimp > 0', ' ', $where);
SELECT DISTINCT
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
					INTO TABLE suc.rep_ger_datos_trabajados
				FROM
					mapuche.dh21 dh21
					JOIN mapuche.dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
					LEFT OUTER JOIN mapuche.dh01 ON (dh01.nro_legaj = dh21.nro_legaj)
					LEFT OUTER JOIN mapuche.dh17 ON (dh17.codn_conce = dh21.codn_conce)
					LEFT OUTER JOIN mapuche.dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
					LEFT OUTER JOIN mapuche.dh11 ON (dh03.codc_categ = dh11.codc_categ)
					LEFT OUTER JOIN mapuche.dh31 ON (dh11.codc_dedic = dh31.codc_dedic)
					LEFT OUTER JOIN mapuche.dh35 ON (dh35.tipo_escal = dh21.tipoescalafon AND dh35.codc_carac = dh03.codc_carac)
				WHERE
					dh21.codn_conce = -51
				GROUP BY
					dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui
				ORDER BY
					dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui;
