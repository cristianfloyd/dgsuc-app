<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    protected $connection = 'pgsql-mapuche';

    public function up(): void
    {
        Schema::create('suc.rep_orden_pago', function (Blueprint $table) {
            $table->id();
            $table->integer('nro_liqui')->nullable();
            $table->integer('banco')->nullable();
            $table->string('codn_funci')->nullable();
            $table->string('codn_fuent')->nullable();
            $table->string('codc_uacad')->nullable();
            $table->text('caracter')->nullable();
            $table->string('codn_progr')->nullable();
            $table->decimal('remunerativo', 15, 2)->nullable();
            $table->decimal('no_remunerativo', 15, 2)->nullable();
            $table->decimal('descuentos', 15, 2)->nullable();
            $table->decimal('aportes', 15, 2)->nullable();
            $table->decimal('sueldo', 15, 2)->nullable();
            $table->decimal('estipendio', 15, 2)->nullable();
            $table->decimal('med_resid', 15, 2)->nullable();
            $table->decimal('productividad', 10, 2)->nullable();
            $table->decimal('sal_fam', 10, 2)->nullable();
            $table->decimal('hs_extras', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->timestamps();
        });

        DB::unprepared("
CREATE OR REPLACE FUNCTION suc.rep_orden_pago(p_nro_liqui INTEGER[])
RETURNS void AS $$
BEGIN
    -- Limpiamos los datos existentes para las liquidaciones específicas
    DELETE FROM suc.rep_orden_pago WHERE nro_liqui = ANY(p_nro_liqui);

    -- Insertamos los nuevos datos
    INSERT INTO suc.rep_orden_pago(
		nro_liqui, banco, codn_funci, codn_fuent, codc_uacad, caracter, codn_progr,
        remunerativo, no_remunerativo, descuentos, aportes,sueldo, estipendio, med_resid,
        productividad, sal_fam, hs_extras, total
	)
    SELECT
    h22.nro_liqui,
	CASE WHEN h92.codn_banco IN (0,1) THEN 0 ELSE 1 END AS banco,
	h21.codn_funci::VARCHAR,
	h21.codn_fuent::VARCHAR,
	h21.codc_uacad::VARCHAR,
	CASE WHEN h03.codc_carac IN ('PERM','PLEN','REGU') THEN 'PERM' ELSE 'CONT' END AS caracter ,  -- personal contratado o permamente
	h21.codn_progr::VARCHAR,
    ROUND(SUM(CASE WHEN h21.tipo_conce= 'C' AND NOT h21.codn_conce IN (121, 122, 124, 125) THEN impp_conce ELSE 0 END)::NUMERIC, 2) AS remunerativo,
    ROUND(SUM(CASE WHEN h21.tipo_conce= 'S' AND NOT h21.codn_conce IN (173, 186) THEN impp_conce ELSE 0 END)::NUMERIC, 2) AS no_remunerativo,
    ROUND(SUM(CASE WHEN h21.tipo_conce= 'D' AND h21.codn_conce/100 = 2 THEN impp_conce ELSE 0 END)::NUMERIC, 2) AS descuentos,
    ROUND(SUM(CASE WHEN h21.tipo_conce= 'A' THEN impp_conce ELSE 0 END)::NUMERIC, 2) AS aportes,
	ROUND((
		ROUND(SUM(CASE WHEN h21.tipo_conce= 'C' AND NOT h21.codn_conce IN (121, 122, 124, 125) THEN impp_conce ELSE 0 END)::NUMERIC, 2)
		+ ROUND(SUM(CASE WHEN h21.tipo_conce= 'S' AND NOT h21.codn_conce IN (173, 186) THEN impp_conce ELSE 0 END)::NUMERIC, 2)
		- ROUND(SUM(CASE WHEN h21.tipo_conce= 'D' AND h21.codn_conce/100 = 2 THEN impp_conce ELSE 0 END)::NUMERIC, 2)
	)::NUMERIC, 2) AS sueldo,
    ROUND(SUM(CASE WHEN h21.codn_conce IN (173) THEN impp_conce ELSE 0 END)::NUMERIC, 2) as estipendio,
	ROUND(SUM(CASE WHEN h21.codn_conce IN (186) THEN impp_conce ELSE 0 END)::NUMERIC, 2) as med_resid,
	0::NUMERIC(10,2) as productividad,
	0::NUMERIC(10,2) as sal_fam,
    ROUND(SUM(CASE WHEN h21.codn_conce IN (121, 122, 124, 125) THEN impp_conce ELSE 0 END)::NUMERIC, 2) AS hs_extras,
    ROUND((
    ROUND(SUM(CASE WHEN h21.tipo_conce= 'C' AND NOT h21.codn_conce IN (121, 122, 124, 125) THEN impp_conce ELSE 0 END)::NUMERIC, 2)
        + ROUND(SUM(CASE WHEN h21.tipo_conce= 'S' AND NOT h21.codn_conce IN (173, 186) THEN impp_conce ELSE 0 END)::NUMERIC, 2)
	    - ROUND(SUM(CASE WHEN h21.tipo_conce= 'D' AND h21.codn_conce/100 = 2 THEN impp_conce ELSE 0 END)::NUMERIC, 2)
	    + ROUND(SUM(CASE WHEN h21.codn_conce IN (173) THEN impp_conce ELSE 0 END)::NUMERIC, 2)
	    + ROUND(SUM(CASE WHEN h21.codn_conce IN (186) THEN impp_conce ELSE 0 END)::NUMERIC, 2)
        + 0
		+ 0
        + SUM(CASE WHEN h21.codn_conce IN (121, 122, 124, 125) THEN impp_conce ELSE 0 END)
    )::NUMERIC, 2) AS total
FROM
	mapuche.dh21 h21
	JOIN mapuche.dh22 h22 ON h21.nro_liqui=h22.nro_liqui
	JOIN mapuche.dh03 h03 ON h21.nro_cargo=h03.nro_cargo
	JOIN mapuche.dh12 h12 ON h21.codn_conce=h12.codn_conce
	LEFT JOIN mapuche.dh92 h92 ON h21.nro_legaj=h92.nrolegajo
WHERE h21.nro_liqui = ANY(p_nro_liqui)
GROUP BY
    h22.nro_liqui,
	banco,
	h21.codn_funci,
	h21.codn_fuent,
	h21.codc_uacad,
	caracter,
	h21.codn_progr
ORDER BY
    h22.nro_liqui,
    banco desc,
	h21.codn_funci,
	h21.codn_fuent,
	h21.codc_uacad,
	caracter,
	h21.codn_progr;
END;
$$ LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suc.rep_orden_pago');
        DB::unprepared("DROP FUNCTION suc.rep_orden_pago(p_nro_liqui INTEGER[])");
    }
};
