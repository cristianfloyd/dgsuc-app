<?php

namespace App\Repositories;

use App\Models\AfipMapucheArt;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class AfipArtRepository.
 *
 * @package App\Repositories
 */
class AfipArtRepository
{
    /**
     * Obtiene todos los registros.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return AfipMapucheArt::all();
    }

    /**
     * Busca un registro por su CUIL original.
     *
     * @param string $cuil
     *
     * @return AfipMapucheArt|null
     */
    public function findByCuil(string $cuil): ?AfipMapucheArt
    {
        return AfipMapucheArt::find($cuil);
    }

    /**
     * Crea un nuevo registro.
     *
     * @param array $data
     *
     * @return AfipMapucheArt
     */
    public function create(array $data): AfipMapucheArt
    {
        return AfipMapucheArt::create($data);
    }

    /**
     * Actualiza un registro existente.
     *
     * @param string $cuil
     * @param array $data
     *
     * @return bool
     */
    public function update(string $cuil, array $data): bool
    {
        $afipArt = AfipMapucheArt::find($cuil);
        return $afipArt ? $afipArt->update($data) : false;
    }

    /**
     * Elimina un registro.
     *
     * @param string $cuil
     *
     * @return bool
     */
    public function delete(string $cuil): bool
    {
        $afipArt = AfipMapucheArt::find($cuil);
        return $afipArt ? $afipArt->delete() : false;
    }
}
