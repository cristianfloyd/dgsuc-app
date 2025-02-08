<?php

namespace App\Repositories;

use App\Models\AfipMapucheArt as AfipArt;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class AfipArtRepository
 *
 * @package App\Repositories
 */
class AfipArtRepository
{
    /**
     * Obtiene todos los registros
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return AfipArt::all();
    }

    /**
     * Busca un registro por su CUIL original
     *
     * @param string $cuil
     * @return AfipArt|null
     */
    public function findByCuil(string $cuil): ?AfipArt
    {
        return AfipArt::find($cuil);
    }

    /**
     * Crea un nuevo registro
     *
     * @param array $data
     * @return AfipArt
     */
    public function create(array $data): AfipArt
    {
        return AfipArt::create($data);
    }

    /**
     * Actualiza un registro existente
     *
     * @param string $cuil
     * @param array $data
     * @return bool
     */
    public function update(string $cuil, array $data): bool
    {
        $afipArt = AfipArt::find($cuil);
        return $afipArt ? $afipArt->update($data) : false;
    }

    /**
     * Elimina un registro
     *
     * @param string $cuil
     * @return bool
     */
    public function delete(string $cuil): bool
    {
        $afipArt = AfipArt::find($cuil);
        return $afipArt ? $afipArt->delete() : false;
    }
}
