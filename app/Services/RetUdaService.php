<?php

namespace App\Services;

use App\Contracts\RetUdaRepository;
use App\DTOs\RetUdaDTO;
use App\Models\Suc\RetUda;
use Illuminate\Database\Eloquent\Collection;

class RetUdaService
{
    /**
     * Constructor del servicio RetUda.
     */
    public function __construct(protected \App\Contracts\RetUdaRepository $repository)
    {
    }

    /**
     * Obtiene todos los registros de RetUda.
     *
     * @return Collection
     */
    public function getAllRetUdas()
    {
        return $this->repository->getAll();
    }

    /**
     * Obtiene un registro de RetUda por su clave primaria compuesta.
     *
     *
     * @return RetUda|null
     */
    public function getRetUda(int $nroLegaj, int $nroCargo, string $periodo): ?RetUdaDTO
    {
        $retUda = $this->repository->findByPrimaryKey($nroLegaj, $nroCargo, $periodo);
        return $retUda ? RetUdaDTO::fromArray($retUda->toArray()) : null;
    }

    /**
     * Crea un nuevo registro de RetUda.
     *
     * @param array $data
     *
     * @return RetUda
     */
    public function createRetUda(RetUdaDTO $dto): RetUdaDTO
    {
        $retUda = $this->repository->create($dto->toArray());
        return RetUdaDTO::fromArray($retUda->toArray());
    }

    /**
     * Actualiza un registro de RetUda.
     *
     * @param array $data
     *
     */
    public function updateRetUda(int $nroLegaj, int $nroCargo, string $periodo, RetUdaDTO $dto): bool
    {
        $retUda = $this->repository->findByPrimaryKey($nroLegaj, $nroCargo, $periodo);
        if (!$retUda) {
            return false;
        }
        return $this->repository->update($retUda, $dto->toArray());
    }

    /**
     * Elimina un registro de RetUda.
     *
     *
     */
    public function deleteRetUda(int $nroLegaj, int $nroCargo, string $periodo): bool
    {
        $retUda = $this->repository->findByPrimaryKey($nroLegaj, $nroCargo, $periodo);
        if (!$retUda) {
            return false;
        }
        return $this->repository->delete($retUda);
    }
}
