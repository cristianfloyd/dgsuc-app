<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql-suc';
    
    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::create('suc.ret_udas', function (Blueprint $table) {
            $table->integer('nro_legaj');
            $table->integer('nro_cargo');
            $table->string('periodo');
            $table->char('tipo_escal', 1)->nullable();
            $table->char('codc_categ', 4)->nullable();
            $table->char('codc_agrup', 4)->nullable();
            $table->char('codc_carac', 4)->nullable();
            $table->float('porc_aplic')->nullable();
            $table->char('codc_dedic', 4)->nullable();
            $table->float('hs_cat')->nullable();
            $table->float('antiguedad')->nullable();
            $table->float('permanencia')->nullable();
            $table->float('porchaber')->nullable();
            $table->string('lic_50')->nullable();
            $table->decimal('impp_basic', 10, 2)->nullable();
            $table->integer('zona_desf')->nullable();
            $table->integer('riesgo')->nullable();
            $table->integer('falla_caja')->nullable();
            $table->integer('ded_excl')->nullable();
            $table->char('titu_nivel', 4)->nullable();
            $table->integer('subrog')->nullable();
            $table->char('cat_108', 4)->nullable();
            $table->decimal('basico_108', 10, 2)->default(0);
            $table->integer('nro_liqui')->nullable();
            $table->decimal('cat_basico_7', 10, 2)->default(0);
            $table->decimal('cat_basico_v_perm', 10, 2)->default(0);
            $table->char('codc_uacad', 3)->nullable();
            $table->char('coddependesemp', 4)->nullable();
            $table->integer('adi_col_sec')->nullable();

            $table->primary(['nro_legaj', 'nro_cargo', 'periodo']);
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('suc.ret_udas');
    }
};
