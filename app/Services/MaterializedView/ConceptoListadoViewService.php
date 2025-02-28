<?php

namespace App\Services\MaterializedView;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;

class ConceptoListadoViewService
{
    use MapucheConnectionTrait;

    public function exists(): bool
    {
        try {
            return (bool) DB::connection($this->getConnectionName())
                ->selectOne("
                    SELECT EXISTS (
                        SELECT 1
                        FROM pg_matviews
                        WHERE schemaname = 'suc'
                        AND matviewname = 'concepto_listado'
                    )
                ")->exists;
        } catch (\Exception $e) {
            Log::error('Error verificando existencia de vista', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function create(): void
    {
        try {
                // sql de la migracion original
                DB::connection($this->getConnectionName())->statement("
                CREATE MATERIALIZED VIEW IF NOT EXISTS suc.concepto_listado AS
                SELECT DISTINCT
                    ROW_NUMBER() OVER () AS id,
                    d.nro_liqui,
                    dh22.desc_liqui,
                    CONCAT(dh22.per_liano, LPAD(dh22.per_limes::TEXT, 2, '0'::TEXT)) AS periodo_fiscal,
                    d.nro_legaj,
                    d.nro_cargo,
                    dh01.desc_appat AS apellido,
                    dh01.desc_nombr AS nombre,
                    CONCAT(dh01.nro_cuil1, lpad(dh01.nro_cuil::text, '8','0'), dh01.nro_cuil2)AS cuil,
                    d.codc_uacad,
                    d.codn_conce,
                    d.impp_conce
                FROM mapuche.dh21 d
                LEFT JOIN mapuche.dh01 ON d.nro_legaj = dh01.nro_legaj
                JOIN mapuche.dh22 ON d.nro_liqui = dh22.nro_liqui
                WHERE d.codn_conce/100 IN (1,2,3);
            ");

                // Indices para optimizar las búsquedas
                DB::connection($this->getConnectionName())->statement(
                    'CREATE INDEX IF NOT EXISTS idx_concepto_listado_codn_conce ON suc.concepto_listado(codn_conce)'
                );
                DB::connection($this->getConnectionName())->statement(
                    'CREATE INDEX IF NOT EXISTS idx_concepto_listado_periodo_fiscal ON suc.concepto_listado(periodo_fiscal)'
                );
            ;

            Log::info('Vista materializada concepto_listado creada exitosamente');
        } catch (\Throwable $e) {
            Log::error('Error creando vista materializada', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }

    }

    public function refresh(): void
    {
        try {
            if (!$this->exists()) {
                Log::info('Vista materializada no existe, creándola...');
                $this->create();
                return;
            }

            DB::connection($this->getConnectionName())->statement(
                'REFRESH MATERIALIZED VIEW suc.concepto_listado'
            );
            Log::info('Vista materializada actualizada exitosamente');
        } catch (\Throwable $th) {
            Log::error('Error actualizando vista materializada', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            throw $th;
        }
    }

    public function drop(): void
    {
        try {
            DB::connection($this->getConnectionName())->statement(
                'DROP MATERIALIZED VIEW IF EXISTS suc.concepto_listado CASCADE'
            );
            Log::info('Vista materializada concepto_listado eliminada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error eliminando vista materializada', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
