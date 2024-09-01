<?php

namespace App\Services;

use App\Models\AfipArt;
use App\Http\Requests\AfipArtRequest;
use App\Repositories\AfipArtRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class AfipArtService
 *
 * @package App\Services
 */
class AfipArtService
{
    protected $afipArtRepository;

    /**
     * AfipArtService constructor.
     *
     * @param AfipArtRepository $afipArtRepository
     */
    public function __construct(AfipArtRepository $afipArtRepository)
    {
        $this->afipArtRepository = $afipArtRepository;
    }

    /**
     * Obtiene todos los registros
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->afipArtRepository->getAll();
    }

    /**
     * Busca un registro por su CUIL original
     *
     * @param string $cuil
     * @return AfipArt|null
     */
    public function findByCuil(string $cuil): ?AfipArt
    {
        return $this->afipArtRepository->findByCuil($cuil);
    }

    /**
     * Crea un nuevo registro
     *
     * @param AfipArtRequest $request
     * @return AfipArt
     */
    public function create(AfipArtRequest $request): AfipArt
    {
        return $this->afipArtRepository->create($request->validated());
    }

    /**
     * Actualiza un registro existente
     *
     * @param string $cuil
     * @param AfipArtRequest $request
     * @return bool
     */
    public function update(string $cuil, AfipArtRequest $request): bool
    {
        return $this->afipArtRepository->update($cuil, $request->validated());
    }

    /**
     * Elimina un registro
     *
     * @param string $cuil
     * @return bool
     */
    public function delete(string $cuil): bool
    {
        return $this->afipArtRepository->delete($cuil);
    }
}
