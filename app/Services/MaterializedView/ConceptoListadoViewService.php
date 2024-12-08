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
            //code...
            DB::connection($this->getConnectionName())->transaction(function (){
                // sql de la migracion original
                DB::connection($this->getConnectionName())->statement("
                CREATE MATERIALIZED VIEW IF NOT EXISTS suc.concepto_listado AS
                WITH legajo_cargo AS (
                    SELECT DISTINCT
                        dh21_1.nro_legaj,
                        dh03.codc_uacad,
                        dh03.nro_cargo
                    FROM mapuche.dh21 dh21_1
                    JOIN mapuche.dh03 ON dh21_1.nro_legaj = dh03.nro_legaj
                )
                SELECT
                    uuid_generate_v4() AS id,
                    dh21.nro_legaj,
                    lc.codc_uacad,
                    CONCAT(dh22.per_liano, LPAD(dh22.per_limes::TEXT, 2, '0'::TEXT)) AS periodo_fiscal,
                    dh22.nro_liqui,
                    dh22.desc_liqui,
                    dh01.desc_appat,
                    dh01.desc_nombr,
                    CONCAT(dh01.nro_cuil1, dh01.nro_cuil, dh01.nro_cuil2) AS cuil,
                    lc.nro_cargo AS secuencia,
                    dh21.codn_conce,
                    dh21.impp_conce
                FROM mapuche.dh21
                JOIN legajo_cargo lc ON dh21.nro_legaj = lc.nro_legaj
                JOIN mapuche.dh01 ON dh21.nro_legaj = dh01.nro_legaj
                JOIN mapuche.dh22 ON dh21.nro_liqui = dh22.nro_liqui
            ");

                // Indices para optimizar las búsquedas
                DB::connection($this->getConnectionName())->statement(
                    'CREATE INDEX IF NOT EXISTS idx_concepto_listado_codn_conce ON suc.concepto_listado(codn_conce)'
                );
                DB::connection($this->getConnectionName())->statement(
                    'CREATE INDEX IF NOT EXISTS idx_concepto_listado_periodo_fiscal ON suc.concepto_listado(periodo_fiscal)'
                );
            });

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
}
