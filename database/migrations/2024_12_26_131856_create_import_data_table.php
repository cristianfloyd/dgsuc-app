<?php

use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use MapucheConnectionTrait;


    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->getConnectionName())->create('suc.rep_import_data', function (Blueprint $table) {
            $table->id();
            $table->timestamp('fecha_registro')->useCurrent();
            $table->string('email')->index();
            $table->string('nombre');
            $table->string('usuario_mapuche')->index();
            $table->string('dependencia');
            $table->integer('nro_legaj')->index();
            $table->integer('nro_cargo')->index();
            $table->date('fecha_baja')->nullable();
            $table->string('tipo');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Índices compuestos para optimizar consultas frecuentes
            $table->index(['nro_legaj', 'nro_cargo']);
            $table->index(['fecha_registro', 'tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.rep_import_data');
    }
};
