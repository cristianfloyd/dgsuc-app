<?php

namespace App\Services\ConceptoListado;

use Illuminate\Database\Connection;

interface ConceptoListadoServiceInterface
{
    /**
     * Obtiene el nombre de la conexión a la base de datos Mapuche.
     */
    public function getConnectionName(): string;

    /**
     * Obtiene la instancia de conexión a la base de datos.
     *
     * @return Connection
     */
    public function getConnection();
}
