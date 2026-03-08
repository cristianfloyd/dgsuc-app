<?php

namespace App\Traits;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

/**
 * Trait SicossConnectionTrait.
 *
 * Versión simplificada del MapucheConnectionTrait para uso en páginas Filament
 */
trait SicossConnectionTrait
{
    /**
     * Obtiene el nombre de la conexión de base de datos.
     */
    public function getConnectionName(): string
    {
        return 'pgsql-prod';
    }

    /**
     * Obtiene la conexión desde el trait.
     *
     * @return Connection
     */
    public function getConnectionFromTrait()
    {
        return DB::connection($this->getConnectionName());
    }
}
