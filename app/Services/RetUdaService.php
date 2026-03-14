<?php

namespace App\Services;

use App\DTOs\RetUdaDTO;
use App\Repositories\RetUdaRepository;
use Illuminate\Database\Eloquent\Collection;

class RetUdaService
{
    public function __construct(
        protected RetUdaRepository $repository,
    ) {}

    /**
     * Obtiene todos los registros de RetUda.
     *
     * @return Collection<int, \App\Models\Suc\RetUda>
     */
    public function getAllRetUdas(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * Obtiene un registro de RetUda por su clave primaria compuesta.
     */
    public function getRetUda(int $nroLegaj, int $nroCargo, string $periodo): ?RetUdaDTO
    {
        $retUda = $this->repository->findByPrimaryKey($nroLegaj, $nroCargo, $periodo);

        return $retUda instanceof \App\Models\Suc\RetUda ? RetUdaDTO::from($retUda->toArray()) : null;
    }

    /**
     * Crea un nuevo registro de RetUda.
     */
    public function createRetUda(RetUdaDTO $dto): RetUdaDTO
    {
        $retUda = $this->repository->create($dto->toArray());

        return RetUdaDTO::from($retUda->toArray());
    }

    /**
     * Actualiza un registro de RetUda.
     */
    public function updateRetUda(int $nroLegaj, int $nroCargo, string $periodo, RetUdaDTO $dto): bool
    {
        $retUda = $this->repository->findByPrimaryKey($nroLegaj, $nroCargo, $periodo);
        if (!$retUda instanceof \App\Models\Suc\RetUda) {
            return false;
        }

        return $this->repository->update($retUda, $dto->toArray());
    }

    /**
     * Elimina un registro de RetUda.
     */
    public function deleteRetUda(int $nroLegaj, int $nroCargo, string $periodo): bool
    {
        $retUda = $this->repository->findByPrimaryKey($nroLegaj, $nroCargo, $periodo);
        if (!$retUda instanceof \App\Models\Suc\RetUda) {
            return false;
        }

        return $this->repository->delete($retUda);
    }
}
