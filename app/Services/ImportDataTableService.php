<?php

namespace App\Services;

use App\Enums\BloqueosEstadoEnum;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ImportDataTableService
{
    use MapucheConnectionTrait;

    /**
     * Verifica y crea la tabla si no existe
     */
    public function ensureTableExists(): void
    {

        if (!Schema::connection($this->getConnectionName())->hasTable('suc.rep_bloqueos_import')) {
            Schema::connection($this->getConnectionName())->create('suc.rep_bloqueos_import', function (Blueprint $table) {
                // Campos de identificación
                $table->id();
                $table->integer('nro_liqui');
                $table->timestamp('fecha_registro')->useCurrent();

                //Datos del solicitante
                $table->string('email');
                $table->string('nombre');
                $table->string('usuario_mapuche');
                $table->string('dependencia');

                // Datos del cargo
                $table->integer('nro_legaj');
                $table->integer('nro_cargo');
                $table->date('fecha_baja')->nullable();
                $table->string('tipo');
                $table->text('observaciones')->nullable();
                $table->boolean('chkstopliq')->default(false);
                $table->boolean('tiene_cargo_asociado')->default(false);
                // Campos de tracking y estado
                $table->string('estado')->default(BloqueosEstadoEnum::IMPORTADO->value);
                $table->text('mensaje_error')->nullable();
                $table->json('datos_validacion')->nullable();
                $table->timestamp('fecha_procesamiento')->nullable();
                $table->string('procesado_por')->nullable();

                // Timestamps estándar
                $table->timestamps();
                $table->softDeletes();

                // Índices para optimización
                $table->index('nro_legaj');
                $table->index('usuario_mapuche');
                $table->index('tipo');
                $table->index('estado');
                $table->index(['nro_legaj', 'nro_cargo']);
                $table->index('fecha_registro');
            });
        }
    }
}
