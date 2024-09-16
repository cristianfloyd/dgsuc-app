<?php

namespace App\Services;

use App\Models\AfipMapucheSicoss;
use App\DTOs\AfipMapucheSicossDTO;
use App\Exceptions\AfipMapucheSicossNotFoundException;
use App\Contracts\AfipMapucheSicossRepositoryInterface;

class AfipMapucheSicossService
{
    private AfipMapucheSicossRepositoryInterface $repository;

    public function __construct(AfipMapucheSicossRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Crea o actualiza un registro de AfipMapucheSicoss.
     *
     * @param AfipMapucheSicossDTO $dto
     * @return AfipMapucheSicoss
     */
    public function createOrUpdate(AfipMapucheSicossDTO $dto)
    {
        $existingRecord = $this->repository->findByPeriodoAndCuil($dto->periodoFiscal, $dto->cuil);

        if ($existingRecord) {
            return $this->repository->update($existingRecord, $dto);
        }

        return $this->repository->create($dto);
    }

    /**
     * Obtiene un registro por período fiscal y CUIL.
     *
     * @param string $periodoFiscal
     * @param string $cuil
     * @return AfipMapucheSicoss
     * @throws AfipMapucheSicossNotFoundException
     */
    public function getByPeriodoAndCuil(string $periodoFiscal, string $cuil)
    {
        $record = $this->repository->findByPeriodoAndCuil($periodoFiscal, $cuil);

        if (!$record) {
            throw new AfipMapucheSicossNotFoundException("No se encontró el registro para el período fiscal $periodoFiscal y CUIL $cuil");
        }

        return $record;
    }
}
