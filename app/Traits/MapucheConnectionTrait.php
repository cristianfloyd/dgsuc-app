<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


/**
 * Trait MapucheConnectionTrait
 *
 * Este trait proporciona la configuración de conexión para la base de datos Mapuche.
 * Se puede utilizar en cualquier modelo que requiera esta conexión específica.
 */
trait MapucheConnectionTrait
{
    /**
     * Obtiene el nombre de la conexión de base de datos.
     * @return string
     */
    public function getConnectionName(): string
    {
        return 'pgsql-mapuche'; // Esto se refiere al nombre de la conexión en config/database.php
    }


    /**
     * Obtiene la conexión desde el trait
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


    public function getTableName(): string
    {
        return $this->getTable();
    }
}
