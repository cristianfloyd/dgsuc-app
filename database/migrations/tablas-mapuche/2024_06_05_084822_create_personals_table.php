<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql-mapuche';
    protected $schema = 'mapuche';
    protected $table = 'mapuche.dh01';
    protected $primaryKey = 'nro_legaj';

    public function up(): void
    {
        Schema::create('mapuche.dh01', function (Blueprint $table) {
            $table->integer('nro_legaj')->primary(); // Número de legajo
            $table->char('desc_appat', 20)->nullable(); //Apellido paterno
            $table->char('desc_apmat', 20)->nullable(); //Apellido materno
            $table->char('desc_apcas', 20)->nullable(); //Apellido de casada
            $table->char('desc_nombr', 20)->nullable(); //Nombre
            $table->integer('nro_tabla')->nullable();
            $table->char('tipo_docum', 4)->nullable();  //Tipo de documento
            $table->integer('nro_docum')->nullable();   //Nro documento
            $table->integer('nro_cuil1')->nullable();
            $table->integer('nro_cuil')->nullable();
            $table->integer('nro_cuil2')->nullable();
            $table->char('tipo_sexo', 1)->nullable();   //Sexo
            $table->date('fec_nacim')->nullable();      //Fecha de nacimiento
            $table->char('tipo_facto', 2)->nullable();  //Factor sanguíneo
            $table->char('tipo_rh', 1)->nullable();     //RH
            $table->integer('nro_ficha')->nullable();   //Nro de ficha
            $table->char('tipo_estad', 1)->nullable();  //Estado civil
            $table->string('nombrelugarnac', 60)->nullable(); //Lugar de nacimiento
            $table->integer('periodoalta')->nullable();
            $table->integer('anioalta')->nullable();
            $table->integer('periodoactualizacion')->nullable();
            $table->integer('anioactualizacion')->nullable();
            $table->char('pcia_nacim', 1)->nullable();      // Provincia Nacimiento
            $table->char('pais_nacim', 2)->nullable();      // Pais Nacimiento

            // Foreign keys
            $table->foreign('pais_nacim')->references('codigo_pais')->on('dha3')->onUpdate('cascade')->deferrable();
            $table->foreign('pcia_nacim')->references('codigo_pcia')->on('dha5')->onUpdate('cascade')->deferrable();

            // Indexs
            $table->index('pais_nacim', 'ix_dh01_k_pais_nacim');
            $table->index('pcia_nacim', 'ix_dh01_k_pcia_nacim');
            $table->index(['desc_appat', 'desc_nombr'], 'ix_dh01_key_desc_apyno');
            $table->unique(['nro_cuil1', 'nro_cuil', 'nro_cuil2'], 'ix_dh01_key_nro_cuil');
            $table->unique(['tipo_docum', 'nro_docum'], 'ix_dh01_key_nro_docum');
            $table->index(['nro_tabla', 'tipo_docum'], 'ix_dh01_key_tipo_docum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapuche.dh01');
    }
};
