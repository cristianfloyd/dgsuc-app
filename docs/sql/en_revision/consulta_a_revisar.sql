EXPLAIN ANALYSE
SELECT
	         --LIQUIDACION
	DISTINCT (dh21.id_liquidacion),
	         dh21.impp_conce,
	         dh21.ano_retro,
	         dh21.mes_retro,
	         --LEGAJO
	         dh01.nro_legaj,
	         --CONCEPTOS
	         dh21.codn_conce,
	         dh21.tipo_conce,
	         dh21.nro_cargo,
	         dh21.nov1_conce,
	         dh12.nro_orimp,
	         -- obtengo un arreglo de los tipos de grupo a los que pertenece el concepto
	         (SELECT (SELECT ARRAY( SELECT DISTINCT codn_tipogrupo
	                                FROM mapuche.dh15
	                                WHERE dh15.codn_grupo IN (SELECT codn_grupo
	                                                          FROM mapuche.dh16
	                                                          WHERE dh16.codn_conce = dh21.codn_conce) ))) AS tipos_grupos,
	         codigoescalafon                                                                               AS codigoescalafon

-- INTO TEMP pre_conceptos_liquidados
FROM (
	     -- UNION de liquidaciones actuales e históricas
	     SELECT id_liquidacion,
	            impp_conce,
	            ano_retro,
	            mes_retro,
	            nro_legaj,
	            codn_conce,
	            tipo_conce,
	            nro_cargo,
	            nov1_conce,
	            nro_liqui,
	            codigoescalafon
	     FROM mapuche.dh21
	     UNION ALL
	     SELECT id_liquidacion,
	            impp_conce,
	            ano_retro,
	            mes_retro,
	            nro_legaj,
	            codn_conce,
	            tipo_conce,
	            nro_cargo,
	            nov1_conce,
	            nro_liqui,
	            codigoescalafon
	     FROM mapuche.dh21h) AS dh21
	     LEFT OUTER JOIN mapuche.dh01 ON (dh01.nro_legaj = dh21.nro_legaj) -- Agentes
	     LEFT OUTER JOIN mapuche.dh12 ON (dh21.codn_conce = dh12.codn_conce) -- Conceptos de Liquidaci n
	     LEFT OUTER JOIN mapuche.dh16 ON (dh16.codn_conce = dh12.codn_conce) -- Grupo al que pertenecen los conceptos
	     LEFT OUTER JOIN mapuche.dh22 ON (dh21.nro_liqui = dh22.nro_liqui) -- Par metros de Liquidaciones
WHERE
-- liquidaciones del periodo vigente y que generen impuestos
	dh22.per_liano = 2025
  AND dh22.per_limes = 5
  AND dh22.sino_genimp
  AND dh21.codn_conce > 0;



-- CONSULTA OPTIMIZADA
-- Crear tabla temporal con tipos_grupos
-- EXPLAIN ANALYSE
CREATE TEMP TABLE pre_conceptos_liquidados AS
WITH tipos_grupos_conceptos AS (
    SELECT
        dh16.codn_conce,
        array_agg(DISTINCT dh15.codn_tipogrupo) AS tipos_grupos
    FROM mapuche.dh16
    INNER JOIN mapuche.dh15 ON dh15.codn_grupo = dh16.codn_grupo
    GROUP BY dh16.codn_conce
)
SELECT DISTINCT
    dh21.id_liquidacion,
    dh21.impp_conce,
    dh21.ano_retro,
    dh21.mes_retro,
    dh21.nro_legaj,
    dh21.codn_conce,
    dh21.tipo_conce,
    dh21.nro_cargo,
    dh21.nov1_conce,
    dh12.nro_orimp,
    COALESCE(tgc.tipos_grupos, ARRAY[]::integer[]) AS tipos_grupos,
    dh21.codigoescalafon
FROM mapuche.dh21
INNER JOIN mapuche.dh22 ON dh22.nro_liqui = dh21.nro_liqui
LEFT JOIN mapuche.dh01 ON dh01.nro_legaj = dh21.nro_legaj
LEFT JOIN mapuche.dh12 ON dh12.codn_conce = dh21.codn_conce
LEFT JOIN tipos_grupos_conceptos tgc ON tgc.codn_conce = dh21.codn_conce
WHERE dh22.per_liano = 2025
  AND dh22.per_limes = 5
  AND dh22.sino_genimp = true
  AND dh21.codn_conce > 0;