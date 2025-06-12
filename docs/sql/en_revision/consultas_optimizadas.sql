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
SELECT DISTINCT(dh01.nro_legaj),
               (dh01.nro_cuil1::CHAR(2) || LPAD( dh01.nro_cuil::CHAR(8), 8, '0' ) ||
                dh01.nro_cuil2::CHAR(1))::float8                                      AS cuit,
               dh01.desc_appat || ' ' || dh01.desc_nombr                              AS apyno,
               dh01.tipo_estad                                                        AS estado,
               COALESCE( familiares.conyugue, 0 )                                     AS conyugue,
               COALESCE( familiares.hijos, 0 )                                        AS hijos,
               dha8.ProvinciaLocalidad,
               dha8.codigosituacion,
               dha8.CodigoCondicion,
               dha8.codigozona,
               dha8.CodigoActividad,
               dha8.porcaporteadicss                                                  AS aporteAdicional,
               dha8.trabajador_convencionado                                          AS trabajadorconvencionado,
               dha8.codigomodalcontrat                                                AS codigocontratacion,

               CASE WHEN ((dh09.codc_bprev = 'REPA') OR (dh09.fuerza_reparto) OR
                          (('REPA' = '') AND (dh09.codc_bprev IS NULL))) THEN '1'
                                                                         ELSE '0' END AS regimen,

               dh09.cant_cargo                                                        AS adherentes,
               0                                                                      AS licencia,
               0                                                                      AS importeimponible_9

FROM mapuche.dh01 dh01
-- ✅ JOIN optimizado para contar familiares
	     LEFT JOIN (SELECT nro_legaj,
	                COUNT( CASE WHEN codc_paren = 'CONY' THEN 1 END )                              AS conyugue,
	                COUNT( CASE WHEN codc_paren IN ( 'HIJO', 'HIJN', 'HINC', 'HINN' ) THEN 1 END ) AS hijos
	         FROM mapuche.dh02
	         WHERE sino_cargo != 'N'
	         GROUP BY nro_legaj) familiares ON familiares.nro_legaj = dh01.nro_legaj
	     LEFT OUTER JOIN mapuche.dha8 ON dha8.nro_legajo = dh01.nro_legaj
	     LEFT OUTER JOIN mapuche.dh09 ON dh09.nro_legaj = dh01.nro_legaj
	     LEFT OUTER JOIN mapuche.dhe9 ON dhe9.nro_legaj = dh01.nro_legaj;