<?php

namespace App\Repositories;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use App\Contracts\DatabaseOperationInterface;

class DatabaseOperationRepository implements DatabaseOperationInterface
{
    use MapucheConnectionTrait;

    /**
     * Constructor del repositorio
     *
     * @param string|null $connectionName Nombre de la conexión a la base de datos
     */
    public function __construct(
        protected ?string $connectionName = null
    ) {}

    /**
     * Obtiene la conexión a la base de datos
     *
     * @return \Illuminate\Database\Connection
     */
    protected function getConnection()
    {
        return DB::connection($this->getConnectionName());
    }

    /**
     * Ejecuta una consulta SQL directa con manejo de errores
     *
     * @param string $sql La consulta SQL a ejecutar
     * @param array $bindings Los parámetros para la consulta
     * @return bool Resultado de la operación
     */
    public function executeQuery(string $sql, array $bindings = []): bool
    {
        try {
            // Validar que la consulta no esté vacía
            if (empty(trim($sql))) {
                Log::warning('Intento de ejecutar consulta SQL vacía');
                return false;
            }

            // Registrar la consulta para auditoría
            Log::info('Ejecutando consulta SQL', [
                'sql' => $sql,
                'bindings' => $bindings,
                'connection' => $this->connectionName ?? 'default'
            ]);

            // Ejecutar la consulta
            $result = $this->getConnection()->statement($sql, $bindings);

            Log::info('Consulta SQL ejecutada exitosamente');
            return $result;

        } catch (Exception $e) {
            // Registrar el error para debugging
            Log::error('Error al ejecutar consulta SQL', [
                'sql' => $sql,
                'bindings' => $bindings,
                'error' => $e->getMessage(),
                'connection' => $this->connectionName ?? 'default'
            ]);

            return false;
        }
    }

    /**
     * Elimina una tabla temporal si existe con validaciones de seguridad
     *
     * @param string $tableName Nombre de la tabla a eliminar
     * @return bool Resultado de la operación
     */
    public function dropTemporaryTable(string $tableName): bool
    {
        try {
            // Validar el nombre de la tabla para prevenir inyección SQL
            if (!$this->isValidTableName($tableName)) {
                Log::warning('Intento de eliminar tabla con nombre inválido', [
                    'table_name' => $tableName
                ]);
                return false;
            }

            // Construir la consulta de forma segura
            $sql = "DROP TABLE IF EXISTS " . $this->escapeTableName($tableName);

            Log::info('Eliminando tabla temporal', [
                'table_name' => $tableName,
                'connection' => $this->connectionName ?? 'default'
            ]);

            $result = $this->getConnection()->statement($sql);

            Log::info('Tabla temporal eliminada exitosamente', [
                'table_name' => $tableName
            ]);

            return $result;

        } catch (Exception $e) {
            Log::error('Error al eliminar tabla temporal', [
                'table_name' => $tableName,
                'error' => $e->getMessage(),
                'connection' => $this->connectionName ?? 'default'
            ]);

            return false;
        }
    }

    /**
     * Verifica si una tabla existe en la base de datos
     *
     * @param string $tableName Nombre de la tabla
     * @return bool True si la tabla existe
     */
    public function tableExists(string $tableName): bool
    {
        try {
            if (!$this->isValidTableName($tableName)) {
                return false;
            }

            $result = $this->getConnection()
                ->getSchemaBuilder()
                ->hasTable($tableName);

            Log::debug('Verificación de existencia de tabla', [
                'table_name' => $tableName,
                'exists' => $result
            ]);

            return $result;

        } catch (Exception $e) {
            Log::error('Error al verificar existencia de tabla', [
                'table_name' => $tableName,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Ejecuta múltiples consultas en una transacción
     *
     * @param array $queries Array de consultas SQL con sus bindings
     * @return bool Resultado de la operación
     */
    public function executeTransaction(array $queries): bool
    {
        try {
            Log::info('Iniciando transacción con múltiples consultas', [
                'query_count' => count($queries)
            ]);

            $result = $this->getConnection()->transaction(function () use ($queries) {
                foreach ($queries as $query) {
                    $sql = $query['sql'] ?? '';
                    $bindings = $query['bindings'] ?? [];

                    if (empty(trim($sql))) {
                        throw new Exception('Consulta SQL vacía en transacción');
                    }

                    $this->getConnection()->statement($sql, $bindings);
                }

                return true;
            });

            Log::info('Transacción completada exitosamente');
            return $result;

        } catch (Exception $e) {
            Log::error('Error en transacción de base de datos', [
                'error' => $e->getMessage(),
                'query_count' => count($queries)
            ]);

            return false;
        }
    }

    /**
     * Valida que el nombre de tabla sea seguro
     *
     * @param string $tableName Nombre de la tabla
     * @return bool True si es válido
     */
    protected function isValidTableName(string $tableName): bool
    {
        // Permitir solo caracteres alfanuméricos y guiones bajos
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $tableName) === 1;
    }

    /**
     * Escapa el nombre de tabla para prevenir inyección SQL
     *
     * @param string $tableName Nombre de la tabla
     * @return string Nombre escapado
     */
    protected function escapeTableName(string $tableName): string
    {
        // Usar el método de escape de Laravel para nombres de tabla
        return $this->getConnection()->getSchemaGrammar()->wrapTable($tableName);
    }
}