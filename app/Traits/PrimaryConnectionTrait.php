<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Trait PrimaryConnectionTrait.
 *
 * Este trait proporciona la configuración de conexión principal para la base de datos.
 * Siempre usa la conexión por defecto de la aplicación (pgsql) sin importar el contexto.
 * Ideal para migraciones y modelos que deben usar la base de datos principal.
 */
trait PrimaryConnectionTrait
{
    /**
     * Obtiene el nombre de la conexión principal de la base de datos.
     * Siempre retorna la conexión por defecto configurada en database.default
     */
    public function getConnectionName(): string
    {
        $primaryConnection = config('database.default');
        
        Log::debug('PrimaryConnectionTrait::getConnectionName', [
            'primary_connection' => $primaryConnection,
            'is_console' => app()->runningInConsole(),
            'context' => 'Always using primary connection'
        ]);

        return $primaryConnection;
    }

    /**
     * Obtiene la conexión principal desde el trait.
     *
     * @return \Illuminate\Database\Connection
     */
    public function getConnectionFromTrait()
    {
        $connectionName = $this->getConnectionName();
        Log::debug('Obteniendo conexión principal desde trait:', ['nombre' => $connectionName]);
        return \Illuminate\Support\Facades\DB::connection($connectionName);
    }

    /**
     * Obtiene el nombre de la tabla calificado con el esquema correspondiente.
     * Para la conexión principal, usa el esquema por defecto.
     */
    public function getTable(): string
    {
        $tableName = parent::getTable();
        
        // Si ya tiene el esquema definido, retornamos tal cual
        if (str_contains($tableName, '.')) {
            return $tableName;
        }

        // Para la conexión principal, usar el esquema por defecto o el configurado
        $schema = $this->schema ?? config('database.connections.pgsql.search_path', 'public');
        
        // Si search_path tiene múltiples esquemas, usar el primero
        if (str_contains($schema, ',')) {
            $schema = explode(',', $schema)[0];
        }

        return "{$schema}.{$tableName}";
    }

    /**
     * Obtiene el nombre de la tabla incluyendo el esquema.
     */
    public function getTableName(): string
    {
        return $this->getTable();
    }
} 