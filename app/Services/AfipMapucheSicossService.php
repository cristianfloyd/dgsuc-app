<?php

namespace App\Services;

use App\Contracts\AfipMapucheSicossRepositoryInterface;
use App\DTOs\AfipMapucheSicossDTO;
use App\Exceptions\AfipMapucheSicossNotFoundException;
use App\Models\AfipMapucheSicoss;

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
     *
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
     *
     * @throws AfipMapucheSicossNotFoundException
     *
     * @return AfipMapucheSicoss
     */
    public function getByPeriodoAndCuil(string $periodoFiscal, string $cuil)
    {
        $record = $this->repository->findByPeriodoAndCuil($periodoFiscal, $cuil);

        if (!$record) {
            throw new AfipMapucheSicossNotFoundException("No se encontró el registro para el período fiscal $periodoFiscal y CUIL $cuil");
        }

        return $record;
    }

    /**
     * Obtiene una lista de períodos fiscales para usar en componentes select.
     *
     * @return array
     */
    public function getPeriodosFiscalesForSelect(): array
    {
        return $this->repository->getDistinctPeriodosFiscales();
    }
}
