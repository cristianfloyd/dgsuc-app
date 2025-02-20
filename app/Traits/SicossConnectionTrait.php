<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Trait SicossConnectionTrait
 *
 * Versión simplificada del MapucheConnectionTrait para uso en páginas Filament
 */
trait SicossConnectionTrait
{
    /**
     * Obtiene el nombre de la conexión de base de datos.
     * @return string
     */
    public function getConnectionName(): string
    {
        return 'pgsql-liqui';
    }

    /**
     * Obtiene la conexión desde el trait
     * @return \Illuminate\Database\Connection
     */
    public function getConnectionFromTrait()
    {
        return DB::connection($this->getConnectionName());
    }
}
