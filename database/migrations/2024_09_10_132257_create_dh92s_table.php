<?php

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use MapucheConnectionTrait;

    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::create('dh92', function (Blueprint $table) {
            $table->integer('autonum')->primary();
            $table->integer('nrolegajo')->nullable();
            $table->integer('codn_banco')->nullable();
            $table->integer('codn_sucur')->nullable();
            $table->char('tipo_cuent', 2)->nullable();
            $table->decimal('nro_cuent', 10, 2)->nullable();
            $table->integer('codn_verif')->nullable();
            $table->integer('nrovalorpago')->nullable();
            $table->string('cbu', 25)->nullable();

            // Claves foráneas
            $table->foreign('nrolegajo')->references('nro_legaj')->on('dh01')->onUpdate('cascade')->deferrable();
            $table->foreign('codn_banco')->references('nroentidadbancaria')->on('dh84')->onUpdate('cascade')->deferrable();
            $table->foreign('nrovalorpago')->references('nrovalorpago')->on('dh91')->onUpdate('cascade')->deferrable();
            $table->foreign(['codn_banco', 'codn_sucur'])->references(['codigo_entbancaria', 'codigo_sucursal'])->on('dha9')->onUpdate('cascade')->deferrable();

            // Índices
            $table->index('codn_banco');
            $table->index(['codn_banco', 'codn_sucur']);
            $table->unique(['nrolegajo', 'nrovalorpago']);
            $table->index('nrolegajo');
            $table->index('nrovalorpago');
        });
    }
    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('dh92');
    }
};
