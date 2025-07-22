<?php

namespace App\Contracts;

use App\Models\Mapuche\Dh19;
use Illuminate\Database\Eloquent\Collection;

interface Dh19RepositoryInterface
{
    /**
     * Obtiene todos los registros de Dh19.
     *
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * Obtiene un registro de Dh19 por su clave primaria compuesta.
     *
     * @param int $nroLegaj
     * @param int $codnConce
     * @param string $tipoDocum
     * @param int $nroDocum
     *
     * @return Dh19|null
     */
    public function findByPrimaryKey(int $nroLegaj, int $codnConce, string $tipoDocum, int $nroDocum): ?Dh19;

    /**
     * Crea un nuevo registro de Dh19.
     *
     * @param array $data
     *
     * @return Dh19
     */
    public function create(array $data): Dh19;

    /**
     * Actualiza un registro de Dh19.
     *
     * @param Dh19 $dh19
     * @param array $data
     *
     * @return bool
     */
    public function update(Dh19 $dh19, array $data): bool;

    /**
     * Elimina un registro de Dh19.
     *
     * @param Dh19 $dh19
     *
     * @return bool
     */
    public function delete(Dh19 $dh19): bool;
}
