<?php

namespace App\Services\ConceptoListado;

interface ConceptoListadoServiceInterface
{
    /**
     * Obtiene el nombre de la conexión a la base de datos Mapuche.
     *
     * @return string
     */
    public function getConnectionName(): string;

    /**
     * Obtiene la instancia de conexión a la base de datos.
     *
     * @return \Illuminate\Database\Connection
     */
    public function getConnection();
}
