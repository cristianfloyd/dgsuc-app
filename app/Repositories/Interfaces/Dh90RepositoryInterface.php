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
     *
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * Buscar por número de cargo.
     *
     * @param int $nroCargo
     *
     * @return Dh90|null
     */
    public function findByNroCargo(int $nroCargo): ?Dh90;

    /**
     * Crear un nuevo registro.
     *
     * @param Dh90Data $data
     *
     * @return Dh90
     */
    public function create(Dh90Data $data): Dh90;

    /**
     * Actualizar un registro existente.
     *
     * @param int $nroCargo
     * @param Dh90Data $data
     *
     * @return Dh90|null
     */
    public function update(int $nroCargo, Dh90Data $data): ?Dh90;

    /**
     * Eliminar un registro.
     *
     * @param int $nroCargo
     *
     * @return bool
     */
    public function delete(int $nroCargo): bool;

    /**
     * Encontrar por tipo de asociación.
     *
     * @param string $tipo
     *
     * @return Collection
     */
    public function findByTipoAsociacion(string $tipo): Collection;
}
