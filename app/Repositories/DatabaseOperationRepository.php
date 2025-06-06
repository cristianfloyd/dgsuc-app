<?php

namespace App\Repositories;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use App\Contracts\DatabaseOperationInterface;
use App\Services\EnhancedDatabaseConnectionService;

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
        try {
            $connectionName = $this->getConnectionName();

            // Verificar que el nombre de conexión no sea null
            if (empty($connectionName)) {
                Log::warning('DatabaseOperationRepository: Nombre de conexión vacío, usando servicio de conexión');
                $service = app(EnhancedDatabaseConnectionService::class);
                $connectionName = $service->getCurrentConnection();
            }

            Log::debug('DatabaseOperationRepository: Usando conexión', [
                'connection_name' => $connectionName,
                'constructor_connection' => $this->connectionName
            ]);

            $connection = DB::connection($connectionName);

            // Verificar que la conexión es válida
            if (!$connection) {
                throw new Exception("No se pudo obtener la conexión '{$connectionName}'");
            }

            return $connection;

        } catch (Exception $e) {
            Log::error('DatabaseOperationRepository: Error al obtener conexión', [
                'error' => $e->getMessage(),
                'connection_name' => $connectionName ?? 'null',
                'constructor_connection' => $this->connectionName ?? 'null'
            ]);

            // Fallback: intentar con conexión por defecto
            $fallbackConnection = EnhancedDatabaseConnectionService::DEFAULT_CONNECTION;
            Log::warning("DatabaseOperationRepository: Usando conexión fallback: {$fallbackConnection}");

            return DB::connection($fallbackConnection);
        }
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
        try {
            $connection = $this->getConnection();

            if (!$connection) {
                Log::warning('DatabaseOperationRepository: Conexión null en escapeTableName, usando escape manual');
                return '"' . str_replace('"', '""', $tableName) . '"';
            }

            $grammar = $connection->getSchemaGrammar();

            if (!$grammar) {
                Log::warning('DatabaseOperationRepository: SchemaGrammar null, usando escape manual');
                return '"' . str_replace('"', '""', $tableName) . '"';
            }

            return $grammar->wrapTable($tableName);

        } catch (Exception $e) {
            Log::error('DatabaseOperationRepository: Error en escapeTableName', [
                'table_name' => $tableName,
                'error' => $e->getMessage()
            ]);

            // Fallback: escape manual para PostgreSQL
            return '"' . str_replace('"', '""', $tableName) . '"';
        }
    }
}
