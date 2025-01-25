<?php

namespace App\Services\Abstract;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use App\Contracts\TableService\TableServiceInterface;

/**
 * Clase abstracta base para servicios de tabla que requieren conexión a Mapuche
 *
 * @package App\Services\Abstract
 */
abstract class AbstractTableService implements TableServiceInterface
{
    use MapucheConnectionTrait;

    /**
     * Verifica la existencia de la tabla
     */
    public function exists(): bool
    {
        return Schema::connection($this->getConnectionName())
            ->hasTable($this->getTableName());
    }

    /**
     * Crea y puebla la tabla dentro de una transacción
     *
     * @throws \Exception
     */
    public function createAndPopulate(): void
    {
        try {
            $this->getConnectionFromTrait()->transaction(function () {
                $this->createTable();
                $this->createIndexes();
                $this->populateTable();
                $this->afterPopulate();
            });

            Log::info("Tabla {$this->getTableName()} creada y poblada exitosamente");
        } catch (\Exception $e) {
            Log::error("Error en createAndPopulate para {$this->getTableName()}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Crea la estructura de la tabla
     */
    protected function createTable(): void
    {
        if (!$this->exists()) {
            Schema::connection($this->getConnectionName())
                ->create($this->getTableName(), function ($table) {
                    $this->addColumns($table);
                });
        }
    }

    /**
     * Agrega las columnas a la tabla
     */
    protected function addColumns($table): void
    {
        foreach ($this->getTableDefinition() as $column => $definition) {
            $this->addColumn($table, $column, $definition);
        }
    }

    /**
     * Agrega una columna específica a la tabla
     */
    protected function addColumn($table, string $column, array $definition): void
    {
        switch ($definition['type']) {
            case 'bigIncrements':
                $table->bigIncrements($column);
                break;
            case 'string':
                $table->string($column, $definition['length'] ?? 255);
                break;
            case 'integer':
                $table->integer($column);
                break;
            case 'decimal':
                $table->decimal($column, $definition['precision'] ?? 8, $definition['scale'] ?? 2);
                break;
            case 'boolean':
                $table->boolean($column);
                break;
            case 'date':
                $table->date($column);
                break;
            case 'datetime':
                $table->datetime($column);
                break;
            case 'timestamp':
                $table->timestamp($column);
                break;
            case 'json':
                $table->json($column);
                break;
            case 'jsonb':
                $table->jsonb($column);
                break;
            default:
                throw new \InvalidArgumentException("Tipo de columna no soportado: {$definition['type']}");
        }

        // Aplicar modificadores si existen
        if (!empty($definition['nullable'])) {
            $table->nullable();
        }
        if (!empty($definition['unique'])) {
            $table->unique();
        }
        if (!empty($definition['default'])) {
            $table->default($definition['default']);
        }
    }

    /**
     * Crea los índices de la tabla
     */
    protected function createIndexes(): void
    {
        Schema::connection($this->getConnectionName())
            ->table($this->getTableName(), function ($table) {
                foreach ($this->getIndexes() as $name => $columns) {
                    $table->index($columns, $name);
                }
            });
    }

    /**
     * Puebla la tabla con datos iniciales
     */
    protected function populateTable(): void
    {
        $query = $this->getTablePopulationQuery();

        if (!empty($query)) {
            $this->getConnectionFromTrait()->statement($query);
        }
    }

    /**
     * Hook para ejecutar acciones después de poblar la tabla
     */
    protected function afterPopulate(): void
    {
        // Implementación opcional en clases hijas
    }

    /**
     * Obtiene la definición de la tabla
     * @return array<string, array>
     */
    abstract protected function getTableDefinition(): array;

    /**
     * Obtiene los índices de la tabla
     * @return array<string, array>
     */
    abstract protected function getIndexes(): array;

    /**
     * Obtiene la consulta SQL para poblar la tabla
     */
    abstract protected function getTablePopulationQuery(): string;

    /**
     * Obtiene el nombre de la tabla
     */
    abstract public function getTableName(): string;
}
