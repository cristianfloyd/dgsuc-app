<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait DynamicConnectionTrait
 *
 * Este trait proporciona una configuración dinámica de conexión para los modelos
 * que necesitan usar la conexión seleccionada por el usuario.
 * Utiliza la conexión 'secondary' que es configurada dinámicamente por el middleware SetDatabaseConnection.
 */
trait DynamicConnectionTrait
{
    /**
     * Obtiene el nombre de la conexión de base de datos.
     * Si 'secondary' no está configurada, usa la conexión predeterminada.
     *
     * @return string
     */
    public function getConnectionName(): string
    {
        // Verificar si la conexión 'secondary' está configurada
        if (Config::has('database.connections.secondary')) {
            return 'secondary';
        }

        // Si no está configurada, usar la conexión predeterminada
        return 'pgsql-prod'; // O cualquier otra conexión predeterminada que prefieras
    }

    /**
     * Obtiene la conexión desde el trait
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
    public function getTable(Model|string $table = null): string
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
     * Obtiene el nombre de la tabla incluyendo el esquema
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->getTable();
    }
}
