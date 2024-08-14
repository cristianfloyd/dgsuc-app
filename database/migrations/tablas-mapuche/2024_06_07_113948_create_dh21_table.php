<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql-mapuche';
    protected $schema = 'mapuche';
    protected $table = 'mapuche.dh21';
    public $timestamps = false;

    public function up(): void
    {
        Schema::create('dh21', function (Blueprint $table) {
            $table->id('id_liquidacion')->primary();
            $table->integer('nro_liqui')->nullable();
            $table->integer('nro_legaj')->nullable();
            $table->integer('nro_cargo')->nullable();
            $table->integer('codn_conce')->nullable();
            $table->double('impp_conce')->nullable();
            $table->char('tipo_conce', 1)->nullable();
            $table->double('nov1_conce')->nullable();
            $table->double('nov2_conce')->nullable();
            $table->integer('nro_orimp')->nullable();
            $table->char('tipoescalafon', 1)->nullable();
            $table->integer('nrogrupoesc')->nullable();
            $table->char('codigoescalafon', 4)->nullable();
            $table->char('codc_regio', 4)->nullable();
            $table->char('codc_uacad', 4)->nullable();
            $table->integer('codn_area')->nullable();
            $table->integer('codn_subar')->nullable();
            $table->integer('codn_fuent')->nullable();
            $table->integer('codn_progr')->nullable();
            $table->integer('codn_subpr')->nullable();
            $table->integer('codn_proye')->nullable();
            $table->integer('codn_activ')->nullable();
            $table->integer('codn_obra')->nullable();
            $table->integer('codn_final')->nullable();
            $table->integer('codn_funci')->nullable();
            $table->integer('ano_retro')->nullable();
            $table->integer('mes_retro')->nullable();
            $table->char('detallenovedad', 10)->nullable();
            $table->integer('codn_grupo_presup')->default(1)->nullable();
            $table->char('tipo_ejercicio', 1)->default('A')->nullable();
            $table->integer('codn_subsubar')->default(0)->nullable();

            // Agregar índices
            $table->index(['nro_legaj', 'nro_cargo'], 'ix_dh21_key_nro_legaj_nro_cargo');
            $table->index('nro_liqui', 'ix_dh21_key_nro_liqui');

            //llaves foraneas
            $table->foreign('nro_liqui')
                  ->references('nro_liqui')
                  ->on('mapuche.dh22')
                  ->onUpdate('cascade')
                  ->deferrable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dh21', function (Blueprint $table) {
            $table->dropIndex('ix_dh21_key_nro_legaj_nro_cargo');
            $table->dropIndex('ix_dh21_key_nro_liqui');
            $table->dropForeign(['dh21_nro_liqui_foreign']);
        });
        Schema::dropIfExists('dh21');
    }
};
