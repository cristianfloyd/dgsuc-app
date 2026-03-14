<?php

namespace App\Repositories;

use App\Models\AfipMapucheArt;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class AfipArtRepository.
 */
class AfipArtRepository
{
    /**
     * Obtiene todos los registros.
     */
    public function getAll(): Collection
    {
        return AfipMapucheArt::all();
    }

    /**
     * Busca un registro por su CUIL original.
     */
    public function findByCuil(string $cuil): ?AfipMapucheArt
    {
        return AfipMapucheArt::query()->find($cuil);
    }

    /**
     * Crea un nuevo registro.
     */
    public function create(array $data): AfipMapucheArt
    {
        return AfipMapucheArt::query()->create($data);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(string $cuil, array $data): bool
    {
        $afipArt = AfipMapucheArt::query()->find($cuil);

        return $afipArt && $afipArt->update($data);
    }

    /**
     * Elimina un registro.
     */
    public function delete(string $cuil): bool
    {
        $afipArt = AfipMapucheArt::query()->find($cuil);

        return $afipArt ? $afipArt->delete() : false;
    }
}
