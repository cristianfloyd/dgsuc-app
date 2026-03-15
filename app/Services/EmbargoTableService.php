<?php

namespace App\Services;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EmbargoTableService
{
    use MapucheConnectionTrait;

    /**
     * Verifica y crea la tabla si no existe.
     */
    public function ensureTableExists(): bool
    {
        try {
            // Verificar que la conexión esté disponible antes de continuar
            if (! $this->isConnectionAvailable()) {
                Log::warning('La conexión de base de datos no está disponible para verificar/crear tabla de embargos');

                return false;
            }

            if (! $this->tableExists()) {
                $this->createTable();
                Log::info('Tabla de embargos creada exitosamente');
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error al verificar/crear tabla de embargos: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Verifica si la conexión de base de datos está disponible.
     */
    private function isConnectionAvailable(): bool
    {
        try {
            $connectionName = $this->getConnectionName();
            $connection = DB::connection($connectionName);

            // Intentar obtener el PDO para verificar la conexión
            $connection->getPdo();

            // Verificar que la base de datos existe ejecutando una consulta simple
            $connection->select('SELECT 1');

            return true;
        } catch (\Exception $e) {
            Log::warning('Conexión no disponible', [
                'connection' => $this->getConnectionName(),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Verifica si la tabla existe.
     */
    private function tableExists(): bool
    {
        return Schema::connection($this->getConnectionName())
            ->hasTable('suc.embargo_proceso_results');
    }

    /**
     * Crea la tabla con su estructura.
     */
    private function createTable(): void
    {
        Schema::connection($this->getConnectionName())
            ->create('suc.embargo_proceso_results', function (Blueprint $table): void {
                $table->id();
                $table->integer('nro_liqui');
                $table->string('tipo_noved', 1)->nullable();
                $table->integer('vig_noano')->nullable();
                $table->integer('vig_nomes')->nullable();
                $table->integer('tipo_embargo');
                $table->integer('nro_legaj');
                $table->decimal('remunerativo', 15, 2);
                $table->decimal('no_remunerativo', 15, 2);
                $table->decimal('total', 20, 2);
                $table->integer('codn_conce');
                $table->string('tipo_foran', 1)->nullable();
                $table->string('clas_noved', 1)->nullable();
                $table->json('nros_liqui_json')->nullable();
                $table->index(['nro_legaj', 'tipo_embargo']);
            });
    }
}
