<?php

namespace App\Contracts\TableService;

/**
 * Interface TableServiceInterface.
 *
 * Define el contrato base para los servicios de gestión de tablas
 */
interface TableServiceInterface
{
    /**
     * Verifica si la tabla existe en la base de datos.
     */
    public function exists(): bool;

    /**
     * Crea la estructura de la tabla si no existe.
     */
    public function createTable(): void;

    /**
     * Puebla la tabla con datos iniciales.
     */
    public function populateTable(): void;

    /**
     * Obtiene el nombre de la tabla.
     */
    public function getTableName(): string;
}
