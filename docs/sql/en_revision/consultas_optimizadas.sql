---------------------------------------------------------------------------------------------------------------------------------
-- 01 PRIMERA CONSULTA - PRE CONCEPTOS LIQUIDADOS
-- ---------------------------------------------------------------------------------------------------------------------------------
CREATE TEMP TABLE pre_conceptos_liquidados AS
-- EXPLAIN ANALYSE
WITH tipos_grupos_conceptos AS (SELECT dh16.codn_conce, ARRAY_AGG( DISTINCT dh15.codn_tipogrupo ) AS tipos_grupos
                                FROM mapuche.dh16
	                                     INNER JOIN mapuche.dh15 ON dh15.codn_grupo = dh16.codn_grupo
                                GROUP BY dh16.codn_conce)
SELECT DISTINCT dh21.id_liquidacion,
                dh21.impp_conce,
                dh21.ano_retro,
                dh21.mes_retro,
                dh21.nro_legaj,
                dh21.codn_conce,
                dh21.tipo_conce,
                dh21.nro_cargo,
                dh21.nov1_conce,
                dh12.nro_orimp,
                COALESCE( tgc.tipos_grupos, ARRAY []::INTEGER[] ) AS tipos_grupos,
                dh21.codigoescalafon
FROM mapuche.dh21
	     INNER JOIN mapuche.dh22 ON dh22.nro_liqui = dh21.nro_liqui
	     LEFT JOIN mapuche.dh01 ON dh01.nro_legaj = dh21.nro_legaj
	     LEFT JOIN mapuche.dh12 ON dh12.codn_conce = dh21.codn_conce
	     LEFT JOIN tipos_grupos_conceptos tgc ON tgc.codn_conce = dh21.codn_conce
WHERE dh22.per_liano = 2025
  AND dh22.per_limes = 5
  AND dh22.sino_genimp = TRUE
  AND dh21.codn_conce > 0;

-- ---------------------------------------------------------------------------------------------------------------------------------
-- 02 SEGUNDA CONSULTA - CONCEPTOS LIQUIDADOS
-- ---------------------------------------------------------------------------------------------------------------------------------
CREATE TEMP VIEW conceptos_liquidados AS
SELECT *
FROM pre_conceptos_liquidados t
WHERE t.ano_retro = :anio_retro
  AND t.mes_retro = :mes_retro;

-- ---------------------------------------------------------------------------------------------------------------------------------
-- 03 TERCERA CONSULTA -  LEGAJOS - get_sql_legajos
-- ---------------------------------------------------------------------------------------------------------------------------------