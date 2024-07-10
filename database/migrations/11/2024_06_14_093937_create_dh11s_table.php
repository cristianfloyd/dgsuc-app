<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql-mapuche';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mapuche.dh11', function (Blueprint $table) {
            $table->char('codc_categ', 4)->primary();
            $table->char('equivalencia', 3)->nullable();
            $table->char('tipo_escal', 1)->nullable();
            $table->integer('nro_escal')->nullable();
            $table->numeric('impp_basic', 10, 2)->nullable();
            $table->char('codc_dedic', 4)->nullable();
            $table->foreign('codc_dedic')->references('codc_dedic')->on('mapuche.dh31')->onUpdate('cascade')->deferrable();
            $table->char('sino_mensu', 1)->nullable();
            $table->char('sino_djpat', 1)->nullable();
            $table->integer('vig_caano')->nullable();
            $table->integer('vig_cames')->nullable();
            $table->char('desc_categ', 20)->nullable();
            $table->char('sino_jefat', 1)->nullable();
            $table->numeric('impp_asign', 10, 2)->nullable();
            $table->integer('computaantig')->nullable();
            $table->boolean('controlcargos')->nullable();
            $table->boolean('controlhoras')->nullable();
            $table->boolean('controlpuntos')->nullable();
            $table->boolean('controlpresup')->nullable();
            $table->char('horasmenanual', 1)->nullable();
            $table->integer('cantpuntos')->nullable();
            $table->char('estadolaboral', 1)->nullable();
            $table->char('nivel', 3)->nullable();
            $table->string('tipocargo', 30)->nullable();
            $table->double('remunbonif')->nullable();
            $table->double('noremunbonif')->nullable();
            $table->double('remunnobonif')->nullable();
            $table->double('noremunnobonif')->nullable();
            $table->double('otrasrem')->nullable();
            $table->double('dto1610')->nullable();
            $table->double('reflaboral')->nullable();
            $table->double('refadm95')->nullable();
            $table->double('critico')->nullable();
            $table->double('jefatura')->nullable();
            $table->double('gastosrepre')->nullable();
            $table->char('codigoescalafon', 4)->nullable();
            $table->integer('noinformasipuver')->nullable();
            $table->integer('noinformasirhu')->default(0);
            $table->integer('imppnooblig')->nullable();
            $table->boolean('aportalao')->nullable();
            $table->foreign('codigoescalafon')->references('codigoescalafon')->on('mapuche.dh89')->deferrable();
            $table->double('factor_hs_catedra')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapuche.dh11');
    }
};
