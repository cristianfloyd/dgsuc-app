<?php

namespace App\Contracts;

use App\Models\AfipMapucheSicoss;
use App\DTOs\AfipMapucheSicossDTO;

interface AfipMapucheSicossRepositoryInterface
{
    /**
     * Encuentra un registro por período fiscal y CUIL.
     *
     * @param string $periodoFiscal
     * @param string $cuil
     * @return AfipMapucheSicoss|null
     */
    public function findByPeriodoAndCuil(string $periodoFiscal, string $cuil): ?AfipMapucheSicoss;

    /**
     * Crea un nuevo registro de AfipMapucheSicoss.
     *
     * @param AfipMapucheSicossDTO $dto
     * @return AfipMapucheSicoss
     */
    public function create(AfipMapucheSicossDTO $dto): AfipMapucheSicoss;

    /**
     * Actualiza un registro existente de AfipMapucheSicoss.
     *
     * @param AfipMapucheSicoss $model
     * @param AfipMapucheSicossDTO $dto
     * @return AfipMapucheSicoss
     */
    public function update(AfipMapucheSicoss $model, AfipMapucheSicossDTO $dto): AfipMapucheSicoss;

    /**
     * Elimina un registro de AfipMapucheSicoss.
     *
     * @param AfipMapucheSicoss $model
     * @return bool
     */
    public function delete(AfipMapucheSicoss $model): bool;
}
