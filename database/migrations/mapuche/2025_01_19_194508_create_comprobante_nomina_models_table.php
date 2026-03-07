<?php

use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use MapucheConnectionTrait;
    public function up(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.comprobantes_nomina');

        Schema::connection($this->getConnectionName())->create('suc.comprobantes_nomina', function (Blueprint $table) {
            $table->id();
            $table->integer('anio_periodo');
            $table->integer('mes_periodo');
            $table->integer('nro_liqui');
            $table->string('desc_liqui', 60);
            $table->string('tipo_pago', 30);
            $table->decimal('importe', 15, 2);
            $table->string('area_administrativa', 3);
            $table->string('subarea_administrativa', 3);
            $table->integer('numero_retencion')->nullable();
            $table->string('descripcion_retencion', 50)->nullable();
            // $table->decimal('importe_retencion', 15, 2)->nullable();
            $table->boolean('requiere_cheque')->default(false);
            $table->string('codigo_grupo', 7)->nullable();
            $table->timestamps();

            $table->index(['anio_periodo', 'mes_periodo']);
            $table->index('nro_liqui');
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.comprobantes_nomina');
    }
};
