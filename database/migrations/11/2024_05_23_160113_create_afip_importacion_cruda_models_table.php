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
        Schema::create('suc.afip_importacion_cruda', function (Blueprint $table) {
            $table->string('linea_completa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suc.afip_importacion_cruda');
    }
};
