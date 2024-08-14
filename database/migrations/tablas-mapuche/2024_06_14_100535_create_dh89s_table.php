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
        Schema::create('mapuche.dh89', function (Blueprint $table) {
            $table->integer('nroesc')->primary();
            $table->char('codigoescalafon', 4)->nullable();
            $table->integer('nroorden')->nullable();
            $table->char('codigoesc', 1)->nullable();
            $table->string('descesc', 30);
            $table->integer('ctrlgradooblig')->nullable();
            $table->char('tipo_perm_tran', 1)->nullable();
            $table->string('infoadiccateg', 5000)->nullable();
            $table->timestamps(); // Opcional, si deseas manejar timestamps en la tabla

            // Índices
            $table->index('codigoesc', 'ix_dh89_key_codigoesc');
            $table->unique('codigoescalafon', 'ix_dh89_key_codigoescalafon');
            $table->index('nroorden', 'ix_dh89_key_nroorden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapuche.dh89');
    }
};
