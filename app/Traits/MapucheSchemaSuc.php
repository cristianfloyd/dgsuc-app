<?php

namespace App\Traits;

trait MapucheSchemaSuc
{
    /**
     * Obtiene el nombre de la conexión de base de datos.
     *
     * @return string
     */
    public function getConnectionName(): string
    {
        return 'pgsql-suc'; // Esto se refiere al nombre de la conexión en config/database.php
    }

    /**
     * Obtiene el nombre de la tabla calificado con el esquema.
     *
     * @return string
     */
    public function getTable()
    {
        $table = parent::getTable();
        return str_starts_with($table, 'suc.') ? $table : "suc.$table";
    }
}
