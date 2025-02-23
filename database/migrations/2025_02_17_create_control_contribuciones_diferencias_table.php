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
        Schema::connection($this->getConnectionName())->dropIfExists('suc.control_contribuciones_diferencias');

        Schema::connection($this->getConnectionName())->create('suc.control_contribuciones_diferencias', function (Blueprint $table) {
            $table->id();
            $table->string('cuil', 11);
            $table->integer('nro_legaj');
            $table->decimal('contribucionsijpdh21', 15, 2);
            $table->decimal('contribucioninssjpdh21', 15, 2);
            $table->decimal('contribucionsijp', 15, 2);
            $table->decimal('contribucioninssjp', 15, 2);
            $table->decimal('diferencia', 15, 2);
            $table->timestamp('fecha_control');
            $table->timestamps();

            $table->index(['cuil', 'nro_legaj']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.control_contribuciones_diferencias');
    }
};
