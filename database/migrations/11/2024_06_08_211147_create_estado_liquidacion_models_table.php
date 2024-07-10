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
        Schema::create('mapuche.estado_liquidacion', function (Blueprint $table) {
            $table->string('codigo_estado_liquidacion', 2)->nullable();
            $table->string('desc_estado_liquidacion', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapuche.estado_liquidacion');
    }
};
