<?php

use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use MapucheConnectionTrait;

    public function up(): void
    {
        Schema::connection($this->getConnectionName())->create('suc.controles_liquidacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_control');
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['pendiente', 'completado', 'error'])->default('pendiente');
            $table->text('resultado')->nullable();
            $table->json('datos_resultado')->nullable();
            $table->integer('nro_liqui');
            $table->timestamp('fecha_ejecucion')->nullable();
            $table->string('ejecutado_por')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.controles_liquidacion');
    }
};
