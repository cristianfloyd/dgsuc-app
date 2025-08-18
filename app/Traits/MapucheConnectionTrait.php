<?php

namespace App\Traits;

use App\Services\DatabaseConnectionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Trait MapucheConnectionTrait.
 *
 * Este trait proporciona la configuración de conexión dinámica para la base de datos.
 * Utiliza la conexión seleccionada por el usuario o la conexión predeterminada.
 */
trait MapucheConnectionTrait
{
    /**
     * Obtiene el nombre de la conexión de base de datos basado en la selección del usuario.
     */
    public function getConnectionName(): string
    {
        // Primero intentamos obtener la conexión de la sesión
        $selectedConnection = Session::get(DatabaseConnectionService::SESSION_KEY);

        // Verificamos si existe la conexión "secondary" configurada por el middleware
        $hasSecondaryConnection = Config::has('database.connections.secondary');

        // Determinamos la conexión predeterminada
        $defaultConnection = DatabaseConnectionService::DEFAULT_CONNECTION;

        // Estrategia de selección de conexión:
        // 1. Si hay una conexión en la sesión y existe en la configuración, usamos esa
        if ($selectedConnection && Config::has("database.connections.{$selectedConnection}")) {
            // Verificar que la base de datos configurada existe
            $dbConfig = Config::get("database.connections.{$selectedConnection}");
            $dbName = $dbConfig['database'] ?? null;

            if (empty($dbName)) {
                Log::warning("La conexión '{$selectedConnection}' no tiene una base de datos configurada, usando predeterminada");
                return $defaultConnection;
            }

            return $selectedConnection;
        }

        // 2. Si existe la conexión "secondary", usamos esa (configurada por el middleware)
        if ($hasSecondaryConnection) {
            // Verificar que la base de datos configurada existe
            $dbConfig = Config::get('database.connections.secondary');
            $dbName = $dbConfig['database'] ?? null;

            if (empty($dbName)) {
                Log::warning("La conexión 'secondary' no tiene una base de datos configurada, usando predeterminada");
                return $defaultConnection;
            }

            Log::debug('Usando conexión secondary configurada por middleware');
            return 'secondary';
        }

        // 3. Como último recurso, usamos la conexión predeterminada
        return $defaultConnection;
    }

    /**
     * Obtiene la conexión desde el trait.
     *
     * @return \Illuminate\Database\Connection
     */
    public function getConnectionFromTrait()
    {
        $connectionName = $this->getConnectionName();
        Log::debug('Obteniendo conexión:', ['nombre' => $connectionName]);
        return DB::connection($connectionName);
    }

    /**
     * Obtiene el nombre de la tabla calificado con el esquema correspondiente.
     *
     * @param Model|string|null $table Tabla opcional
     */
    public function getTable(?Model $table = null): string
    {
        $tableName = parent::getTable();
        // Si ya tiene el esquema definido, retornamos tal cual
        if (str_contains($tableName, '.')) {
            return $tableName;
        }

        // Usar el esquema definido en el modelo o 'mapuche' como fallback
        $schema = $this->schema ?? 'mapuche';

        return "{$schema}.{$tableName}";
    }

    /**
     * Obtiene el nombre de la tabla incluyendo el esquema.
     */
    public function getTableName(): string
    {
        return $this->getTable();
    }

    /**
     * Obtiene la conexión desde el trait.
     *
     * @return \Illuminate\Database\Connection
     */
    public function getMapucheConnection()
    {
        $selectedConnection = Session::get(DatabaseConnectionService::SESSION_KEY);

        // Verificamos si existe la conexión "secondary" configurada por el middleware
        $hasSecondaryConnection = Config::has('database.connections.secondary');

        // Determinamos la conexión predeterminada
        $defaultConnection = DatabaseConnectionService::DEFAULT_CONNECTION;

        // 1. Si hay una conexión en la sesión y existe en la configuración, usamos esa
        if ($selectedConnection && Config::has("database.connections.{$selectedConnection}")) {
            // Verificar que la base de datos configurada existe
            $dbConfig = Config::get("database.connections.{$selectedConnection}");
            $dbName = $dbConfig['database'] ?? null;

            if (empty($dbName)) {
                Log::warning("La conexión '{$selectedConnection}' no tiene una base de datos configurada, usando predeterminada");
                return DB::connection($defaultConnection);
            }

            return DB::connection($selectedConnection);
        }

        // 2. Si existe la conexión "secondary", usamos esa (configurada por el middleware)
        if ($hasSecondaryConnection) {
            // Verificar que la base de datos configurada existe
            $dbConfig = Config::get('database.connections.secondary');
            $dbName = $dbConfig['database'] ?? null;

            if (empty($dbName)) {
                Log::warning("La conexión 'secondary' no tiene una base de datos configurada, usando predeterminada");
                return DB::connection($defaultConnection);
            }

            return DB::connection('secondary');
        }

        // 3. Como último recurso, usamos la conexión predeterminada
        return DB::connection($defaultConnection);
    }
}
