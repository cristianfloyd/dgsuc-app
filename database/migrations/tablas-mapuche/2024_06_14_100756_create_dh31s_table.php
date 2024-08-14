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
        Schema::create('mapuche.dh31', function (Blueprint $table) {
            $table->char('codc_dedic', 4)->primary();
            $table->char('desc_dedic', 20)->nullable();
            $table->integer('cant_horas')->nullable();
            $table->char('tipo_horas', 1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapuche.dh31');
    }
};
