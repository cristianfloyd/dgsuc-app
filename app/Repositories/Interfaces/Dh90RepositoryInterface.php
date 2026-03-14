<?php

namespace App\Repositories\Interfaces;

use App\Data\Dh90Data;
use App\Models\Dh90;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interfaz para repositorio de Dh90.
 */
interface Dh90RepositoryInterface
{
    /**
     * Obtener todos los registros.
     */
    public function getAll(): Collection;

    /**
     * Buscar por número de cargo.
     *
     *
     */
    public function findByNroCargo(int $nroCargo): ?Dh90;

    /**
     * Crear un nuevo registro.
     *
     *
     */
    public function create(Dh90Data $data): Dh90;

    /**
     * Actualizar un registro existente.
     *
     *
     */
    public function update(int $nroCargo, Dh90Data $data): ?Dh90;

    /**
     * Eliminar un registro.
     *
     *
     */
    public function delete(int $nroCargo): bool;

    /**
     * Encontrar por tipo de asociación.
     *
     *
     */
    public function findByTipoAsociacion(string $tipo): Collection;
}
