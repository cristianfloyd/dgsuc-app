DROP TABLE dh21aporte;

SELECT mapuche.dh21.nro_legaj,
       (nro_cuil1::CHAR(2) || LPAD( nro_cuil::CHAR(8), 8, '0' ) || nro_cuil2::CHAR(1))                         AS cuil,
       SUM( CASE WHEN codn_conce IN ( 201, 202, 203, 205, 204, 403 ) THEN impp_conce * 1
                                                                     ELSE impp_conce * 0 END )::NUMERIC::money AS aportesijpdh21,
       SUM( CASE WHEN codn_conce IN ( 247, 447 ) THEN impp_conce * 1
                                                 ELSE impp_conce * 0 END )::NUMERIC::money                     AS aporteinssjpdh21,
       SUM( CASE WHEN codn_conce IN ( 301, 302, 303, 304, 307 ) THEN impp_conce * 1
                                                                ELSE impp_conce * 0 END )::NUMERIC::money      AS contribucionsijpdh21,
       SUM( CASE WHEN codn_conce IN (347) THEN impp_conce * 1 ELSE impp_conce * 0 END )::NUMERIC::money        AS contribucioninssjpdh21
INTO TEMP dh21aporte
FROM mapuche.dh21,
     mapuche.dh01
WHERE nro_liqui IN ( 36, 37 )
  AND mapuche.dh21.nro_legaj = mapuche.dh01.nro_legaj --and mapuche.dh21h.nro_legaj = 110830
GROUP BY mapuche.dh21.nro_legaj, nro_cuil1, nro_cuil, nro_cuil2

SELECT a.nro_legaj,
       a.cuil,
       a.aportesijpdh21,
       a.aporteinssjpdh21,
       b.aportesijp,
       b.aporteinssjp,
       b.aportediferencialsijp,
       b.aportesres33_41re,
       (aportesijpdh21 + aporteinssjpdh21)::NUMERIC -
       (aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re) AS dif
--into suc.z_difaporte06
FROM dh21aporte a,
     suc.afip_mapuche_sicoss_calculos b
WHERE a.cuil = b.cuil
  AND ABS( ((aportesijpdh21 + aporteinssjpdh21)::NUMERIC -
            (aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re)) ) > 1
--and a.nro_legaj not in (select nro_legaj from mapuche.dh21h where codn_conce in(205))
ORDER BY dif