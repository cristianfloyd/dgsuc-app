<?php

namespace App\Contracts;

use App\Models\Mapuche\Dh19;
use Illuminate\Database\Eloquent\Collection;

interface Dh19RepositoryInterface
{
    /**
     * Obtiene todos los registros de Dh19.
     */
    public function getAll(): Collection;

    /**
     * Obtiene un registro de Dh19 por su clave primaria compuesta.
     */
    public function findByPrimaryKey(int $nroLegaj, int $codnConce, string $tipoDocum, int $nroDocum): ?Dh19;

    /**
     * Crea un nuevo registro de Dh19.
     */
    public function create(array $data): Dh19;

    /**
     * Actualiza un registro de Dh19.
     */
    public function update(Dh19 $dh19, array $data): bool;

    /**
     * Elimina un registro de Dh19.
     */
    public function delete(Dh19 $dh19): bool;
}
