-- 1. Comparar cantidad total de registros
SELECT 'Original' as fuente, COUNT(*) as total_registros FROM preconceptos_liquidados
UNION ALL
SELECT 'Optimizada' as fuente, COUNT(*) as total_registros FROM preconceptos_liquidados_simple;



-- 2. Comparar conceptos únicos
SELECT 'Original' as fuente, COUNT(DISTINCT codn_conce) as conceptos_unicos 
FROM preconceptos_liquidados
UNION ALL
SELECT 'Optimizada' as fuente, COUNT(DISTINCT codn_conce) as conceptos_unicos 
FROM preconceptos_liquidados_simple;

-- 3. Comparar legajos únicos
SELECT 'Original' as fuente, COUNT(DISTINCT nro_legaj) as legajos_unicos 
FROM preconceptos_liquidados
UNION ALL
SELECT 'Optimizada' as fuente, COUNT(DISTINCT nro_legaj) as legajos_unicos 
FROM preconceptos_liquidados_simple;

-- 4. Comparar sumas de importes (para verificar integridad)
SELECT 'Original' as fuente, 
       SUM(impp_conce) as suma_importes,
       AVG(impp_conce) as promedio_importes
FROM preconceptos_liquidados
UNION ALL
SELECT 'Optimizada' as fuente, 
       SUM(impp_conce) as suma_importes,
       AVG(impp_conce) as promedio_importes
FROM preconceptos_liquidados_simple;

----------------------------------------------------------------------------------------


-- 1. Comparar cantidad total de registros
SELECT 'Original (suc.pre_conceptos_liquidados)' as fuente, COUNT(*) as total_registros
FROM suc.pre_conceptos_liquidados
UNION ALL
SELECT 'Optimizada con tipos_grupos' as fuente, COUNT(*) as total_registros
FROM temp_con_tipos_grupos;

-- 2. Comparar conceptos únicos
SELECT 'Original (suc.pre_conceptos_liquidados)' as fuente, COUNT(DISTINCT codn_conce) as conceptos_unicos
FROM suc.pre_conceptos_liquidados
UNION ALL
SELECT 'Optimizada con tipos_grupos' as fuente, COUNT(DISTINCT codn_conce) as conceptos_unicos
FROM temp_con_tipos_grupos;

-- 3. Comparar legajos únicos
SELECT 'Original (suc.pre_conceptos_liquidados)' as fuente, COUNT(DISTINCT nro_legaj) as legajos_unicos
FROM suc.pre_conceptos_liquidados
UNION ALL
SELECT 'Optimizada con tipos_grupos' as fuente, COUNT(DISTINCT nro_legaj) as legajos_unicos
FROM temp_con_tipos_grupos;

-- 4. Comparar sumas de importes
SELECT 'Original (suc.pre_conceptos_liquidados)' as fuente,
       ROUND(SUM(impp_conce)::numeric, 2) as suma_importes,
       ROUND(AVG(impp_conce)::numeric, 2) as promedio_importes
FROM suc.pre_conceptos_liquidados
UNION ALL
SELECT 'Optimizada con tipos_grupos' as fuente,
       ROUND(SUM(impp_conce)::numeric, 2) as suma_importes,
       ROUND(AVG(impp_conce)::numeric, 2) as promedio_importes
FROM temp_con_tipos_grupos;

-- 5. Verificar diferencias en registros individuales
SELECT 'Solo en Original' as diferencia, COUNT(*) as cantidad
FROM suc.pre_conceptos_liquidados orig
LEFT JOIN temp_con_tipos_grupos opt ON opt.id_liquidacion = orig.id_liquidacion
WHERE opt.id_liquidacion IS NULL

UNION ALL

SELECT 'Solo en Optimizada' as diferencia, COUNT(*) as cantidad
FROM temp_con_tipos_grupos opt
LEFT JOIN suc.pre_conceptos_liquidados orig ON orig.id_liquidacion = opt.id_liquidacion
WHERE orig.id_liquidacion IS NULL;

-- 6. Comparar algunos tipos_grupos específicos (muestra)
SELECT
    'Original' as fuente,
    codn_conce,
    tipos_grupos
FROM suc.pre_conceptos_liquidados
WHERE codn_conce IN (101, 303, 831, 214, 310)
  AND tipos_grupos IS NOT NULL
ORDER BY codn_conce
LIMIT 10;

-- 7. Comparar tipos_grupos de la optimizada
SELECT
    'Optimizada' as fuente,
    codn_conce,
    tipos_grupos
FROM temp_con_tipos_grupos
WHERE codn_conce IN (101, 303, 831, 214, 310)
  AND tipos_grupos IS NOT NULL
ORDER BY codn_conce
LIMIT 10;