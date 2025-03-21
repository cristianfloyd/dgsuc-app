<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class EmbargoTableService
{
    use MapucheConnectionTrait;

    /**
     * Verifica y crea la tabla si no existe
     *
     * @return bool
     */
    public function ensureTableExists(): bool
    {
        try {
            if (!$this->tableExists()) {
                $this->createTable();
                Log::info('Tabla de embargos creada exitosamente');
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Error al verificar/crear tabla de embargos: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si la tabla existe
     *
     * @return bool
     */
    private function tableExists(): bool
    {
        return Schema::connection($this->getConnectionName())
            ->hasTable('suc.embargo_proceso_results');
    }

    /**
     * Crea la tabla con su estructura
     *
     * @return void
     */
    private function createTable(): void
    {
        Schema::connection($this->getConnectionName())
            ->create('suc.embargo_proceso_results', function (Blueprint $table) {
                $table->id();
                $table->integer('nro_liqui');
                $table->string('tipo_noved', 1)->nullable();
                $table->integer('vig_noano')->nullable();
                $table->integer('vig_nomes')->nullable();
                $table->integer('tipo_embargo');
                $table->integer('nro_legaj');
                $table->decimal('remunerativo', 10, 2);
                $table->decimal('no_remunerativo', 10, 2);
                $table->decimal('total', 10, 2);
                $table->integer('codn_conce');
                $table->string('tipo_foran', 1)->nullable();
                $table->string('clas_noved', 1)->nullable();
                $table->index(['nro_legaj', 'tipo_embargo']);
            });
    }
}
