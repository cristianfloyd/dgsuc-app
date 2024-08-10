<?php

namespace App\Traits;

/**
 * Trait MapucheConnectionTrait
 *
 * Este trait proporciona la configuración de conexión para la base de datos Mapuche.
 * Se puede utilizar en cualquier modelo que requiera esta conexión específica.
 */
trait MapucheConnectionTrait
{
    /**
     * El nombre de la conexión de base de datos que se utilizará para este modelo.
     *
     * @var string
     */
    protected $connection = 'pgsql-mapuche';

    /**
     * Obtiene el nombre de la conexión de base de datos.
     * @return string
     */
    public function getConnectionName(): string
    {
        return $this->connection;
    }
}
