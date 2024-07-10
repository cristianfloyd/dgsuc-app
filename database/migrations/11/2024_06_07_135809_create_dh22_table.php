<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql-mapuche';


    public function up(): void
    {
        Schema::create('mapuche.dh22', function (Blueprint $table) {
            $table->integer('nro_liqui')->primary();
            $table->integer('per_liano')->nullable();
            $table->integer('per_limes')->nullable();
            $table->string('desc_liqui', 60)->nullable();
            $table->date('fec_ultap')->nullable();
            $table->integer('per_anoap')->nullable();
            $table->integer('per_mesap')->nullable();
            $table->char('desc_lugap', 20)->nullable();
            $table->date('fec_emisi')->nullable();
            $table->char('desc_emisi', 20)->nullable();
            $table->integer('vig_emano')->nullable();
            $table->integer('vig_emmes')->nullable();
            $table->integer('vig_caano')->nullable();
            $table->integer('vig_cames')->nullable();
            $table->integer('vig_coano')->nullable();
            $table->integer('vig_comes')->nullable();
            $table->integer('codn_econo')->nullable();
            $table->char('sino_cerra', 1)->notNullable();
            $table->boolean('sino_aguin')->nullable();
            $table->boolean('sino_reten')->nullable();
            $table->boolean('sino_genimp')->nullable();
            $table->integer('nrovalorpago')->nullable();
            $table->integer('finimpresrecibos')->nullable();
            $table->integer('id_tipo_liqui')->default(1)->notNullable();

            // Agregar índices
            $table->index(['per_liano', 'per_limes'], 'ix_dh22_key_per_liqui');

            // Foreign key constraints
            $table->foreign('id_tipo_liqui')
                  ->references('id')
                  ->on('mapuche.dh22_tipos')
                  ->deferrable();

            /**
             * Agrega una restricción de clave externa a la columna 'sino_cerra'.
             * La clave externa hace referencia a la columna 'cod_estado_liquidacion' en la tabla 'mapuche.estado_liquidacion'.
             * La restricción es aplazable, lo que significa que se puede verificar al final de la transacción.
             */
            $table->foreign('sino_cerra')
                ->references('cod_estado_liquidacion')
                ->on('mapuche.estado_liquidacion')
                ->deferrable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mapuche.dh22', function (Blueprint $table) {
            $table->dropForeign(['id_tipo_liqui']);
            $table->dropForeign(['sino_cerra']);
            $table->dropIndex('ix_dh22_key_per_liqui');
        });
        Schema::dropIfExists('mapuche.dh22');
    }
};
