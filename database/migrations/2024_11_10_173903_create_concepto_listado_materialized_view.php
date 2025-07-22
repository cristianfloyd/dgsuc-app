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
        // // Primero creamos la secuencia
        // DB::connection($this->getConnectionName())->statement("
        //     CREATE SEQUENCE IF NOT EXISTS suc.concepto_listado_id_seq;
        // ");

        // DB::connection($this->getConnectionName())->statement("
        //     CREATE MATERIALIZED VIEW suc.concepto_listado AS
        //     WITH legajo_cargo AS (SELECT DISTINCT dh21_1.nro_legaj,
        //                             dh03.codc_uacad,
        //                             dh03.nro_cargo
        //             FROM mapuche.dh21 dh21_1
        //                     JOIN mapuche.dh03 ON dh21_1.nro_legaj = dh03.nro_legaj)
        //     SELECT
        //         nextval('suc.concepto_listado_id_seq') AS id,
        //         dh21.nro_legaj,
        //         lc.codc_uacad,
        //         CONCAT(dh22.per_liano, LPAD(dh22.per_limes::TEXT, 2, '0'::TEXT))                          AS periodo_fiscal,
        //         dh22.nro_liqui,
        //         dh22.desc_liqui,
        //         dh01.desc_appat,
        //         dh01.desc_nombr,
        //         CONCAT(dh01.nro_cuil1, dh01.nro_cuil, dh01.nro_cuil2)                                     AS cuil,
        //         lc.nro_cargo                                                                              AS secuencia,
        //         dh21.codn_conce,
        //         dh21.impp_conce
        //     FROM mapuche.dh21
        //         JOIN legajo_cargo lc ON dh21.nro_legaj = lc.nro_legaj
        //         JOIN mapuche.dh01 ON dh21.nro_legaj = dh01.nro_legaj
        //         JOIN mapuche.dh22 ON dh21.nro_liqui = dh22.nro_liqui
        // ");

        // // Creamos índices para optimizar las búsquedas
        // DB::connection($this->getConnectionName())->statement('CREATE INDEX idx_concepto_listado_codn_conce ON suc.concepto_listado(codn_conce)');
        // DB::connection($this->getConnectionName())->statement('CREATE INDEX idx_concepto_listado_periodo_fiscal ON suc.concepto_listado(periodo_fiscal)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection($this->getConnectionName())->statement('DROP MATERIALIZED VIEW IF EXISTS suc.concepto_listado');
        DB::connection($this->getConnectionName())->statement('DROP SEQUENCE IF EXISTS suc.concepto_listado_id_seq');
    }
};
