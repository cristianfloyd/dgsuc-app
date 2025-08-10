<?php

namespace App\Traits;

/**
 * Trait MapucheLiquiConnectionTrait.
 *
 * Este trait proporciona la configuración de conexión para la base de datos Mapuchito.
 * Se puede utilizar en cualquier modelo que requiera esta conexión específica.
 */
trait MapucheLiquiConnectionTrait
{
    /**
     * Obtiene el nombre de la conexión de base de datos.
     */
    public function getConnectionName(): string
    {
        return 'pgsql-liqui'; // Esto se refiere al nombre de la conexión en config/database.php
    }

    /**
     * Obtiene el nombre de la tabla calificado con el esquema.
     *
     * @return string
     */
    public function getTable()
    {
        $table = parent::getTable();
        return str_starts_with($table, 'mapuche.') ? $table : "mapuche.$table";
    }
}
