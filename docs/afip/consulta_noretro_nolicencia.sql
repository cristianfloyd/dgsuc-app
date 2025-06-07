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

INTO TEMP pre_conceptos_liquidados
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
	     LEFT OUTER JOIN mapuche.dh12 ON (dh21.codn_conce = dh12.codn_conce) -- Conceptos de Liquidaci�n
	     LEFT OUTER JOIN mapuche.dh16 ON (dh16.codn_conce = dh12.codn_conce) -- Grupo al que pertenecen los conceptos
	     LEFT OUTER JOIN mapuche.dh22 ON (dh21.nro_liqui = dh22.nro_liqui) -- Par�metros de Liquidaciones
WHERE
-- liquidaciones del periodo vigente y que generen impuestos
	dh22.per_liano = 2025
  AND dh22.per_limes = 5
  AND dh22.sino_genimp
  AND dh21.codn_conce > 0
  AND dh01.nro_legaj = 110830
  AND dh22.nro_liqui = '31';

DROP TABLE IF EXISTS pre_conceptos_liquidados;
SELECT *
FROM pre_conceptos_liquidados;


SELECT *
INTO TEMP conceptos_liquidados
FROM pre_conceptos_liquidados t
WHERE TRUE;

SELECT DISTINCT(dh01.nro_legaj),
               (dh01.nro_cuil1::CHAR(2) || LPAD( dh01.nro_cuil::CHAR(8), 8, '0' ) ||
                dh01.nro_cuil2::CHAR(1))::float8                                    AS cuit,
               dh01.desc_appat || ' ' || dh01.desc_nombr                            AS apyno,
               dh01.tipo_estad                                                      AS estado,
               (SELECT COUNT( * )
                FROM mapuche.dh02
                WHERE dh02.nro_legaj = dh01.nro_legaj
	               AND dh02.sino_cargo != 'N'
	               AND dh02.codc_paren = 'CONY')                                     AS conyugue,
               (SELECT COUNT( * )
                FROM mapuche.dh02
                WHERE dh02.nro_legaj = dh01.nro_legaj
	               AND dh02.sino_cargo != 'N'
	               AND dh02.codc_paren IN ( 'HIJO', 'HIJN', 'HINC', 'HINN' ))        AS hijos,
               dha8.ProvinciaLocalidad,
               dha8.codigosituacion,
               dha8.CodigoCondicion,
               dha8.codigozona,
               dha8.CodigoActividad,
               dha8.porcaporteadicss                                                AS aporteAdicional,
               dha8.trabajador_convencionado                                        AS trabajadorconvencionado,
               dha8.codigomodalcontrat                                              AS codigocontratacion,
               CASE WHEN ((dh09.codc_bprev = NULL) OR (dh09.fuerza_reparto) OR
                          ((NULL = '') AND (dh09.codc_bprev IS NULL))) THEN '1'
                                                                       ELSE '0' END AS regimen,
               dh09.cant_cargo                                                      AS adherentes,
               0                                                                    AS licencia,
               0                                                                    AS importeimponible_9
FROM conceptos_liquidados
	     LEFT OUTER JOIN mapuche.dh02 ON dh02.nro_legaj = conceptos_liquidados.nro_legaj
	     LEFT OUTER JOIN mapuche.dha8 ON dha8.nro_legajo = conceptos_liquidados.nro_legaj
	     LEFT OUTER JOIN mapuche.dh09 ON dh09.nro_legaj = conceptos_liquidados.nro_legaj
	     LEFT OUTER JOIN mapuche.dhe9 ON dhe9.nro_legaj = conceptos_liquidados.nro_legaj
	     LEFT OUTER JOIN mapuche.dh01 ON conceptos_liquidados.nro_legaj = dh01.nro_legaj
WHERE TRUE;

SELECT DISTINCT(dh01.nro_legaj),
               (dh01.nro_cuil1::CHAR(2) || LPAD( dh01.nro_cuil::CHAR(8), 8, '0' ) ||
                dh01.nro_cuil2::CHAR(1))::float8                                    AS cuit,
               dh01.desc_appat || ' ' || dh01.desc_nombr                            AS apyno,
               dh01.tipo_estad                                                      AS estado,
               (SELECT COUNT( * )
                FROM mapuche.dh02
                WHERE dh02.nro_legaj = dh01.nro_legaj
	               AND dh02.sino_cargo != 'N'
	               AND dh02.codc_paren = 'CONY')                                     AS conyugue,
               (SELECT COUNT( * )
                FROM mapuche.dh02
                WHERE dh02.nro_legaj = dh01.nro_legaj
	               AND dh02.sino_cargo != 'N'
	               AND dh02.codc_paren IN ( 'HIJO', 'HIJN', 'HINC', 'HINN' ))        AS hijos,
               dha8.ProvinciaLocalidad,
               dha8.codigosituacion,
               dha8.CodigoCondicion,
               dha8.codigozona,
               dha8.CodigoActividad,
               dha8.porcaporteadicss                                                AS aporteAdicional,
               dha8.trabajador_convencionado                                        AS trabajadorconvencionado,
               dha8.codigomodalcontrat                                              AS codigocontratacion,
               CASE WHEN ((dh09.codc_bprev = NULL) OR (dh09.fuerza_reparto) OR
                          ((NULL = '') AND (dh09.codc_bprev IS NULL))) THEN '1'
                                                                       ELSE '0' END AS regimen,
               dh09.cant_cargo                                                      AS adherentes,
               0                                                                    AS licencia,
               0                                                                    AS importeimponible_9
FROM conceptos_liquidados
	     LEFT OUTER JOIN mapuche.dh02 ON dh02.nro_legaj = conceptos_liquidados.nro_legaj
	     LEFT OUTER JOIN mapuche.dha8 ON dha8.nro_legajo = conceptos_liquidados.nro_legaj
	     LEFT OUTER JOIN mapuche.dh09 ON dh09.nro_legaj = conceptos_liquidados.nro_legaj
	     LEFT OUTER JOIN mapuche.dhe9 ON dhe9.nro_legaj = conceptos_liquidados.nro_legaj
	     LEFT OUTER JOIN mapuche.dh01 ON conceptos_liquidados.nro_legaj = dh01.nro_legaj
WHERE TRUE
ORDER BY apyno;