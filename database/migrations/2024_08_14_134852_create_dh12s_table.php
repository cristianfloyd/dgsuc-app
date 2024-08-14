<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::create('mapuche.dh12', function (Blueprint $table) {
            // Definición de columnas
            $table->integer('codn_conce')->primary();
            $table->integer('vig_coano')->nullable();
            $table->integer('vig_comes')->nullable();
            $table->string('desc_conce', 30)->nullable();
            $table->char('desc_corta', 15)->nullable();
            $table->char('tipo_conce', 1)->default('C')->nullable(false);
            $table->char('codc_vige1', 1)->nullable();
            $table->char('desc_nove1', 15)->nullable();
            $table->char('tipo_nove1', 1)->nullable();
            $table->integer('cant_ente1')->nullable();
            $table->integer('cant_deci1')->nullable();
            $table->char('codc_vige2', 1)->nullable();
            $table->char('desc_nove2', 15)->nullable();
            $table->char('tipo_nove2', 1)->nullable();
            $table->integer('cant_ente2')->nullable();
            $table->integer('cant_deci2')->nullable();
            $table->string('flag_acumu', 99)->nullable();
            $table->string('flag_grupo', 90)->nullable();
            $table->integer('nro_orcal')->nullable(false);
            $table->integer('nro_orimp')->nullable(false);
            $table->char('sino_legaj', 1)->default('N')->nullable(false);
            $table->char('tipo_distr', 1)->nullable();
            $table->integer('tipo_ganan')->nullable();
            $table->boolean('chk_acumsac')->nullable();
            $table->boolean('chk_acumproy')->nullable();
            $table->boolean('chk_dcto3')->nullable();
            $table->boolean('chkacumprhbrprom')->nullable();
            $table->integer('subcicloliquida')->nullable();
            $table->boolean('chkdifhbrcargoasoc')->nullable();
            $table->boolean('chkptesubconcep')->nullable();
            $table->boolean('chkinfcuotasnovper')->nullable();
            $table->boolean('genconimp0')->nullable();
            $table->integer('sino_visible')->nullable();

            // Índices
            $table->index('desc_conce', 'ix_dh12_key_desc_conce');
            $table->index(['sino_legaj', 'nro_orcal'], 'ix_dh12_key_nro_orcal');
            $table->index('nro_orimp', 'ix_dh12_key_nro_orimp');
            $table->index(['tipo_conce', 'desc_corta'], 'ix_dh12_key_tipo_conce');
        });

        // Crear trigger
        DB::unprepared('
            CREATE TRIGGER tauditoria_dh12
            AFTER INSERT OR DELETE OR UPDATE ON mapuche.dh12
            FOR EACH ROW
            EXECUTE FUNCTION mapuche_auditoria.sp_dh12();
        ');
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapuche.dh12');
    }
};
