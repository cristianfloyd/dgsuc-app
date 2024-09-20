<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql-mapuche';

    public function up(): void
    {
        Schema::create('dh19', function (Blueprint $table) {
            $table->integer('nro_legaj');
            $table->integer('codn_conce');
            $table->integer('nro_tabla')->nullable();
            $table->char('tipo_docum', 4);
            $table->integer('nro_docum');
            $table->string('desc_apell', 30)->nullable();
            $table->string('desc_nombre', 30)->nullable();
            $table->decimal('porc_benef', 5, 2)->nullable();

            $table->primary(['nro_legaj', 'codn_conce', 'tipo_docum', 'nro_docum']);

            $table->foreign('codn_conce')
                  ->references('codn_conce')
                  ->on('dh12')
                  ->onUpdate('cascade')
                  ->deferrable();

            $table->foreign(['nro_tabla', 'tipo_docum'])
                  ->references(['nro_tabla', 'desc_abrev'])
                  ->on('dh30')
                  ->onUpdate('cascade')
                  ->deferrable();

            $table->index('codn_conce');
            $table->index('nro_legaj');
            $table->index(['nro_tabla', 'tipo_docum']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dh19');
    }
};
