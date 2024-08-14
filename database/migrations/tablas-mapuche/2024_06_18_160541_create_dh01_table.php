<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql-mapuche';
    protected $table = 'mapuche.dh01';
    protected $schema = 'mapuche';
    protected $primaryKey = 'nro_legaj';
    public $timestamps = false;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dh01', function (Blueprint $table) {
            $table->integer('nro_legaj')->primary();
            $table->char('desc_appat', 20)->nullable();
            $table->char('desc_apmat', 20)->nullable();
            $table->char('desc_apcas', 20)->nullable();
            $table->char('desc_nombr', 20)->nullable();
            $table->integer('nro_tabla')->nullable();
            $table->char('tipo_docum', 4)->nullable();
            $table->integer('nro_docum')->nullable();
            $table->integer('nro_cuil1')->nullable();
            $table->integer('nro_cuil')->nullable();
            $table->integer('nro_cuil2')->nullable();
            $table->char('tipo_sexo', 1)->nullable();
            $table->date('fec_nacim')->nullable();
            $table->char('tipo_facto', 2)->nullable();
            $table->char('tipo_rh', 1)->nullable();
            $table->integer('nro_ficha')->nullable();
            $table->char('tipo_estad', 1)->nullable();
            $table->string('nombrelugarnac', 60)->nullable();
            $table->integer('periodoalta')->nullable();
            $table->integer('anioalta')->nullable();
            $table->integer('periodoactualizacion')->nullable();
            $table->integer('anioactualizacion')->nullable();
            $table->char('pcia_nacim', 1)->nullable();
            $table->char('pais_nacim', 2)->nullable();

            // $table->foreign('pais_nacim')->references('codigo_pais')->on('mapuche.dha3')->onUpdate('cascade')->deferrable();
            // $table->foreign('pcia_nacim')->references('codigo_pcia')->on('mapuche.dha5')->onUpdate('cascade')->deferrable();
            $table->index(['nro_cuil1', 'nro_cuil', 'nro_cuil2'], 'cuil_completo_index');
            $table->foreign('cuil_completo')->references('cuil')->on('suc.afip_mapuche_sicoss');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dh01');
    }
};
