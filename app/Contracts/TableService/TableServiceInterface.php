<?php

namespace App\Contracts\TableService;

/**
 * Interface TableServiceInterface.
 *
 * Define el contrato base para los servicios de gestión de tablas
 *
 * @package App\Contracts\TableService
 */
interface TableServiceInterface
{
    /**
     * Verifica si la tabla existe en la base de datos.
     *
     * @return bool
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
     *
     * @return string
     */
    public function getTableName(): string;
}
