<?php

namespace App\Services;

use App\Http\Requests\AfipArtRequest;
use App\Models\AfipMapucheArt;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class AfipArtService.
 */
class AfipArtService
{
    /**
     * AfipArtService constructor.
     */
    public function __construct(protected \App\Repositories\AfipArtRepository $afipArtRepository) {}

    /**
     * Obtiene todos los registros.
     */
    public function getAll(): Collection
    {
        return $this->afipArtRepository->getAll();
    }

    /**
     * Busca un registro por su CUIL original.
     */
    public function findByCuil(string $cuil): ?AfipMapucheArt
    {
        return $this->afipArtRepository->findByCuil($cuil);
    }

    /**
     * Crea un nuevo registro.
     */
    public function create(AfipArtRequest $request): AfipMapucheArt
    {
        return $this->afipArtRepository->create($request->validated());
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(string $cuil, AfipArtRequest $request): bool
    {
        return $this->afipArtRepository->update($cuil, $request->validated());
    }

    /**
     * Elimina un registro.
     */
    public function delete(string $cuil): bool
    {
        return $this->afipArtRepository->delete($cuil);
    }
}
