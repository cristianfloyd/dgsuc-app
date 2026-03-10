<?php

namespace App\Services;

use App\Contracts\AfipMapucheSicossRepositoryInterface;
use App\DTOs\AfipMapucheSicossDTO;
use App\Exceptions\AfipMapucheSicossNotFoundException;
use App\Models\AfipMapucheSicoss;

class AfipMapucheSicossService
{
    public function __construct(private readonly AfipMapucheSicossRepositoryInterface $repository)
    {
    }

    /**
     * Crea o actualiza un registro de AfipMapucheSicoss.
     *
     *
     */
    public function createOrUpdate(AfipMapucheSicossDTO $dto): \App\Models\AfipMapucheSicoss
    {
        $existingRecord = $this->repository->findByPeriodoAndCuil($dto->periodoFiscal, $dto->cuil);

        if ($existingRecord instanceof \App\Models\AfipMapucheSicoss) {
            return $this->repository->update($existingRecord, $dto);
        }

        return $this->repository->create($dto);
    }

    /**
     * Obtiene un registro por período fiscal y CUIL.
     *
     *
     * @throws AfipMapucheSicossNotFoundException
     *
     * @return AfipMapucheSicoss
     */
    public function getByPeriodoAndCuil(string $periodoFiscal, string $cuil)
    {
        $record = $this->repository->findByPeriodoAndCuil($periodoFiscal, $cuil);

        if (!$record instanceof \App\Models\AfipMapucheSicoss) {
            throw new AfipMapucheSicossNotFoundException("No se encontró el registro para el período fiscal $periodoFiscal y CUIL $cuil");
        }

        return $record;
    }

    /**
     * Obtiene una lista de períodos fiscales para usar en componentes select.
     */
    public function getPeriodosFiscalesForSelect(): array
    {
        return $this->repository->getDistinctPeriodosFiscales();
    }
}
