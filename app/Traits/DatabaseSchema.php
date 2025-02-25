<?php

namespace App\Traits;

/**
 * Trait para manejar modelos que utilizan esquemas específicos en PostgreSQL.
 */
trait DatabaseSchema
{
    /**
     * Obtiene el nombre completo de la tabla incluyendo el esquema.
     *
     * @return string
     */
    public function getQualifiedTableName()
    {
        return $this->schema ? "{$this->schema}.{$this->table}" : $this->table;
    }

    /**
     * Sobreescribe el método getTable para incluir el esquema en consultas Eloquent.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->getQualifiedTableName();
    }
}
