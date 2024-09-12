<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('suc.rep_orden_pago', function (Blueprint $table) {
            $table->integer('nro_liqui')->nullable();
            $table->integer('banco')->nullable();
            $table->string('codn_funci')->nullable();
            $table->string('codn_fuent')->nullable();
            $table->string('codc_uacad')->nullable();
            $table->text('caracter')->nullable();
            $table->string('codn_progr')->nullable();
            $table->decimal('remunerativo', 15, 2)->nullable();
            $table->decimal('no_remunerativo', 15, 2)->nullable();
            $table->decimal('descuentos', 15, 2)->nullable();
            $table->decimal('aportes', 15, 2)->nullable();
            $table->decimal('estipendio', 15, 2)->nullable();
            $table->decimal('med_resid', 15, 2)->nullable();
            $table->decimal('productividad', 10, 2)->nullable();
            $table->decimal('sal_fam', 10, 2)->nullable();
            $table->decimal('hs_extras', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suc.rep_orden_pago');
    }
};
