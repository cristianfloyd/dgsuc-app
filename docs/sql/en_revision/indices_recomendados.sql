-- Índices para optimizar las consultas
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_dh22_periodo_genimp 
ON mapuche.dh22 (per_liano, per_limes, sino_genimp) 
WHERE sino_genimp = true;

CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_dh21_nro_liqui_codn_conce 
ON mapuche.dh21 (nro_liqui, codn_conce) 
WHERE codn_conce > 0;

CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_dh21h_nro_liqui_codn_conce 
ON mapuche.dh21h (nro_liqui, codn_conce) 
WHERE codn_conce > 0;

CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_dh16_codn_grupo 
ON mapuche.dh16 (codn_grupo);

CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_dh15_codn_grupo 
ON mapuche.dh15 (codn_grupo);