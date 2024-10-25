<?php

namespace App\Services;

use App\Contracts\RetUdaRepository;
use App\DTOs\RetUdaDTO;
use App\Models\Suc\RetUda;

class RetUdaService
{
    protected $repository;

    /**
     * Constructor del servicio RetUda.
     *
     * @param RetUdaRepository $repository
     */
    public function __construct(RetUdaRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Obtiene todos los registros de RetUda.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllRetUdas()
    {
        return $this->repository->getAll();
    }

    /**
     * Obtiene un registro de RetUda por su clave primaria compuesta.
     *
     * @param int $nroLegaj
     * @param int $nroCargo
     * @param string $periodo
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
     * @param int $nroLegaj
     * @param int $nroCargo
     * @param string $periodo
     * @param array $data
     * @return bool
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
     * @param int $nroLegaj
     * @param int $nroCargo
     * @param string $periodo
     * @return bool
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
