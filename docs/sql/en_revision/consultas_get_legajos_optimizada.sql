--------------------------------------------------------------------
-- ORIGINAL
EXPLAIN ANALYSE
SELECT DISTINCT(dh01.nro_legaj),
               (dh01.nro_cuil1::CHAR(2) || LPAD( dh01.nro_cuil::CHAR(8), 8, '0' ) ||
                dh01.nro_cuil2::CHAR(1))::float8                                      AS cuit,
               dh01.desc_appat || ' ' || dh01.desc_nombr                              AS apyno,
               dh01.tipo_estad                                                        AS estado,
               (SELECT COUNT( * )
                FROM mapuche.dh02
                WHERE dh02.nro_legaj = dh01.nro_legaj
	               AND dh02.sino_cargo != 'N'
	               AND dh02.codc_paren = 'CONY')                                       AS conyugue,
               (SELECT COUNT( * )
                FROM mapuche.dh02
                WHERE dh02.nro_legaj = dh01.nro_legaj
	               AND dh02.sino_cargo != 'N'
	               AND dh02.codc_paren IN ( 'HIJO', 'HIJN', 'HINC', 'HINN' ))          AS hijos,
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
	     LEFT OUTER JOIN mapuche.dh02 ON dh02.nro_legaj = dh01.nro_legaj
	     LEFT OUTER JOIN mapuche.dha8 ON dha8.nro_legajo = dh01.nro_legaj
	     LEFT OUTER JOIN mapuche.dh09 ON dh09.nro_legaj = dh01.nro_legaj
	     LEFT OUTER JOIN mapuche.dhe9 ON dhe9.nro_legaj = dh01.nro_legaj;

------------------------------------------------------------------------------------------------------
-- OPTIMIZADA
EXPLAIN ANALYSE
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