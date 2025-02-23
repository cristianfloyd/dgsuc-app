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
        Schema::connection($this->getConnectionName())->dropIfExists('suc.control_cuils_diferencias');
        Schema::connection($this->getConnectionName())->dropIfExists('suc.control_aportes_diferencias');
        Schema::connection($this->getConnectionName())->dropIfExists('suc.control_art_diferencias');

        Schema::connection($this->getConnectionName())->create('suc.control_cuils_diferencias', function (Blueprint $table) {
            $table->id();
            $table->string('cuil', 11);
            $table->string('origen', 10);
            $table->timestamp('fecha_control');
            $table->string('connection')->nullable();
            $table->index(['cuil', 'fecha_control']);
        });

        Schema::connection($this->getConnectionName())->create('suc.control_aportes_diferencias', function (Blueprint $table) {
            $table->id();
            $table->string('cuil', 11);
            $table->string('codc_uacad', 3)->nullable();
            $table->string('caracter', 4)->nullable();
            $table->decimal('aportesijpdh21', 12, 2);
            $table->decimal('aporteinssjpdh21', 12, 2);
            $table->decimal('diferencia', 12, 2);
            $table->timestamp('fecha_control');
            $table->string('connection')->nullable();
            $table->index(['cuil', 'fecha_control']);
        });

        Schema::connection($this->getConnectionName())->create('suc.control_art_diferencias', function (Blueprint $table) {
            $table->id();
            $table->string('cuil', 11);
            $table->decimal('art_contrib', 12, 2);
            $table->decimal('calculo_teorico', 12, 2);
            $table->decimal('diferencia', 12, 2);
            $table->timestamp('fecha_control');
            $table->string('connection')->nullable();
            $table->index(['cuil', 'fecha_control']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.control_cuils_diferencias');
        Schema::connection($this->getConnectionName())->dropIfExists('suc.control_aportes_diferencias');
        Schema::connection($this->getConnectionName())->dropIfExists('suc.control_art_diferencias');
    }
};
