<?php

namespace App\Traits;

/**
 * Trait MapucheConnectionTrait
 *
 * Este trait proporciona la configuración de conexión para la base de datos Mapuche.
 * Se puede utilizar en cualquier modelo que requiera esta conexión específica.
 */
trait MapucheDesaConnectionTrait
{
    /**
     * Obtiene el nombre de la conexión de base de datos.
     * @return string
     */
    public function getConnectionName(): string
    {
        return 'pgsql-desa'; // Esto se refiere al nombre de la conexión en config/database.php
    }

    /**
     * Obtiene el nombre de la tabla calificado con el esquema.
     *
     * @return string
     */
    public function getTable()
    {
        $table = parent::getTable();
        return strpos($table, 'mapuche.') === 0 ? $table : "mapuche.$table";
    }
}
