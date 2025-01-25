<?php

namespace App\Contracts\Tables;

/**
 * Interfaz base para definiciones de tabla
 */
interface AbstractTableDefinitionInterface
{
    /**
     * Obtiene el nombre de la tabla incluyendo el esquema
     */
    public function getTableName(): string;

    /**
     * Obtiene la definición de columnas
     * @return array<string, array>
     */
    public function getColumns(): array;

    /**
     * Obtiene la definición de índices
     * @return array<string, array>
     */
    public function getIndexes(): array;
}
