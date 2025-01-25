<?php

namespace App\Contracts\TableService;

/**
 * Interface TableServiceInterface
 *
 * Define el contrato base para los servicios de gestión de tablas
 *
 * @package App\Contracts\TableService
 */
interface TableServiceInterface
{
    /**
     * Verifica si la tabla existe en la base de datos
     *
     * @return bool
     */
    public function exists(): bool;

    /**
     * Crea y puebla la tabla con datos iniciales
     *
     * @return void
     * @throws \Exception Si hay error en la creación o población
     */
    public function createAndPopulate(): void;

    /**
     * Obtiene el nombre de la tabla
     *
     * @return string
     */
    public function getTableName(): string;
}
