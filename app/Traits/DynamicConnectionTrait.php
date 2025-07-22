<?php

namespace App\Traits;

use App\Services\DatabaseConnectionService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Trait DynamicConnectionTrait.
 *
 * Este trait proporciona una configuración dinámica de conexión para los modelos
 * que necesitan usar la conexión seleccionada por el usuario.
 * Utiliza la conexión 'secondary' que es configurada dinámicamente por el middleware SetDatabaseConnection.
 */
trait DynamicConnectionTrait
{
    /**
     * Obtiene el nombre de la conexión de base de datos basado en la selección del usuario.
     *
     * @return string
     */
    public function getConnectionName(): string
    {
        // Obtener la conexión seleccionada de la sesión
        $selectedConnection = Session::get(DatabaseConnectionService::SESSION_KEY);

        // Verificar si la conexión seleccionada existe y es válida
        if ($selectedConnection && Config::has("database.connections.{$selectedConnection}")) {
            Log::debug('conexion seleccionada:', ['' => $selectedConnection]);
            return $selectedConnection;
        }
        Log::debug('conexion fallback predeterminada:', ['' => DatabaseConnectionService::DEFAULT_CONNECTION]);
        // Si no hay conexión seleccionada o no es válida, usar la conexión predeterminada
        return DatabaseConnectionService::DEFAULT_CONNECTION;
    }

    /**
     * Obtiene la conexión desde el trait.
     *
     * @return \Illuminate\Database\Connection
     */
    public function getConnectionFromTrait()
    {
        return DB::connection($this->getConnectionName());
    }

    /**
     * Obtiene el nombre de la tabla calificado con el esquema correspondiente.
     *
     * @return string
     */
    public function getTable(?string $table = null): string
    {
        $table = parent::getTable();
        // Si ya tiene el esquema definido, retornamos tal cual
        if (str_contains($table, '.')) {
            return $table;
        }

        // Usar el esquema definido en el modelo o 'mapuche' como fallback
        $schema = $this->schema ?? 'mapuche';

        return "{$schema}.{$table}";
    }

    /**
     * Obtiene el nombre de la tabla incluyendo el esquema.
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->getTable();
    }
}
