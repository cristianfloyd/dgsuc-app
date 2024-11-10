<?php

use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use MapucheConnectionTrait;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::connection($this->getConnectionName())->statement("
            CREATE MATERIALIZED VIEW mapuche.concepto_listado AS
            WITH legajo_cargo AS (
                SELECT DISTINCT ON (dh21.nro_legaj)
                    dh21.nro_legaj,
                    dh03.coddependesemp,
                    dh03.codc_uacad,
                    dh03.nro_cargo
                FROM mapuche.dh21
                INNER JOIN mapuche.dh03 ON dh21.nro_legaj = dh03.nro_legaj
                WHERE dh03.chkstopliq = 0
            )
            SELECT
                CONCAT(dh21.nro_legaj,'-',lc.coddependesemp,'-',dh21.nro_liqui,'-',dh21.codn_conce) as id,
                dh21.nro_legaj,
                lc.codc_uacad,
                CONCAT(dh22.per_liano, LPAD(CAST(dh22.per_limes AS TEXT), 2, '0')) as periodo_fiscal,
                dh22.nro_liqui,
                dh22.desc_liqui,
                dh01.desc_appat,
                dh01.desc_nombr,
                CONCAT(dh01.nro_cuil1, dh01.nro_cuil, dh01.nro_cuil2) AS cuil,
                lc.coddependesemp,
                lc.nro_cargo AS secuencia,
                dh21.codn_conce,
                dh21.tipo_conce,
                dh21.impp_conce
            FROM mapuche.dh21
            INNER JOIN legajo_cargo lc ON dh21.nro_legaj = lc.nro_legaj
            INNER JOIN mapuche.dh01 ON dh21.nro_legaj = dh01.nro_legaj
            INNER JOIN mapuche.dh22 ON dh21.nro_liqui = dh22.nro_liqui
        ");

        // Creamos índices para optimizar las búsquedas
        DB::connection($this->getConnectionName())->statement('CREATE INDEX idx_concepto_listado_codn_conce ON mapuche.concepto_listado(codn_conce)');
        DB::connection($this->getConnectionName())->statement('CREATE INDEX idx_concepto_listado_periodo_fiscal ON mapuche.concepto_listado(periodo_fiscal)');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection($this->getConnectionName())->statement('DROP MATERIALIZED VIEW IF EXISTS mapuche.concepto_listado');
    }
};
