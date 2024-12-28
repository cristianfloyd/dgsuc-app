<?php

namespace App\Services;

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

        if (!Schema::connection($this->getConnectionName())->hasTable('suc.rep_import_data')) {
            Schema::connection($this->getConnectionName())->create('suc.rep_import_data', function (Blueprint $table) {
                $table->id();
                $table->integer('nro_liqui');
                $table->timestamp('fecha_registro');
                $table->string('email');
                $table->string('nombre');
                $table->string('usuario_mapuche');
                $table->string('dependencia');
                $table->integer('nro_legaj');
                $table->integer('nro_cargo');
                $table->date('fecha_baja')->nullable();
                $table->string('tipo');
                $table->text('observaciones')->nullable();
                $table->boolean('chkstopliq')->default(false);
                $table->timestamps();

                // Índices para mejorar el rendimiento
                $table->index('nro_legaj');
                $table->index('usuario_mapuche');
                $table->index('tipo');
            });
        }
    }
}
