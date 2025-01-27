<?php

namespace App\Services\Abstract;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use App\Exceptions\TableStructureException;
use App\Contracts\TableService\TableServiceInterface;

/**
 * Clase base abstracta para servicios de gestión de tablas
 *
 * Esta clase implementa el patrón Template Method para manejar:
 * - Creación de estructura de tabla
 * - Validación de estructura
 * - Población de datos
 * - Manejo de transacciones y errores
 */
abstract class AbstractTableService implements TableServiceInterface
{
    use MapucheConnectionTrait;


    /**
     * Valida la estructura actual de la tabla contra la definición esperada
     *
     * @throws TableStructureException
     */
    public function validateStructure(): void
    {
        $currentColumns = Schema::connection($this->getConnectionName())
            ->getColumnListing($this->getTableName());

        $expectedColumns = array_keys($this->getTableDefinition());

        $missingColumns = array_diff($expectedColumns, $currentColumns);
        $extraColumns = array_diff($currentColumns, $expectedColumns);

        if (!empty($missingColumns) || !empty($extraColumns)) {
            throw new TableStructureException(
                $this->getTableName(),
                $missingColumns,
                $extraColumns
            );
        }

        // Validar tipos de datos y propiedades de columnas
        foreach ($this->getTableDefinition() as $column => $definition) {
            $this->validateColumnDefinition($column, $definition);
        }
    }

    /**
     * Valida la definición de una columna específica
     *
     * @throws TableStructureException
     */
    protected function validateColumnDefinition(string $column, array $definition): void
    {
        $columnType = Schema::connection($this->getConnectionName())
            ->getColumnType($this->getTableName(), $column);

        // Mapeo de tipos de columna Laravel a tipos de base de datos
        $expectedType = $this->mapColumnType($definition['type']);

        if ($columnType !== $expectedType) {
            throw new TableStructureException(
                $this->getTableName(),
                [],
                [],
                "Tipo de columna incorrecto para {$column}: esperado {$expectedType}, actual {$columnType}"
            );
        }
    }

    /**
     * Mapea tipos de columna de Laravel a tipos de base de datos
     */
    protected function mapColumnType(string $laravelType): string
    {
        $typeMap = [
            'string' => 'varchar',
            'integer' => 'integer',
            'decimal' => 'numeric',
            'boolean' => 'boolean',
            'date' => 'date',
            'datetime' => 'timestamp',
            'timestamp' => 'timestamp',
            'json' => 'json',
            'jsonb' => 'jsonb'
        ];

        return $typeMap[$laravelType] ?? $laravelType;
    }


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
    public function createTable(): void
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
        $columnDefinition = null;
        switch ($definition['type']) {
            case 'bigIncrements':
                $columnDefinition = $table->bigIncrements($column);
                break;
            case 'string':
                $columnDefinition = $table->string($column, $definition['length'] ?? 255);
                break;
            case 'integer':
                $columnDefinition = $table->integer($column);
                break;
            case 'decimal':
                $columnDefinition = $table->decimal($column, $definition['precision'] ?? 8, $definition['scale'] ?? 2);
                break;
            case 'boolean':
                $columnDefinition = $table->boolean($column);
                break;
            case 'date':
                $columnDefinition = $table->date($column);
                break;
            case 'datetime':
                $columnDefinition = $table->datetime($column);
                break;
            case 'timestamp':
                $columnDefinition = $table->timestamp($column);
                break;
            case 'json':
                $columnDefinition = $table->json($column);
                break;
            case 'jsonb':
                $columnDefinition = $table->jsonb($column);
                break;
            default:
                throw new \InvalidArgumentException("Tipo de columna no soportado: {$definition['type']}");
        }

        // Aplicar modificadores si existen
        if (!empty($definition['nullable'])) {
            $columnDefinition->nullable();
        }
        if (!empty($definition['unique'])) {
            $columnDefinition->unique();
        }
        if (isset($definition['default'])) {
            $columnDefinition->default($definition['default']);
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
    public function populateTable(): void
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
