<?php

namespace App\Contracts;

use App\DTOs\AfipMapucheSicossDTO;
use App\Models\AfipMapucheSicoss;

interface AfipMapucheSicossRepositoryInterface
{
    /**
     * Encuentra un registro por período fiscal y CUIL.
     */
    public function findByPeriodoAndCuil(string $periodoFiscal, string $cuil): ?AfipMapucheSicoss;

    /**
     * Crea un nuevo registro de AfipMapucheSicoss.
     */
    public function create(AfipMapucheSicossDTO $dto): AfipMapucheSicoss;

    /**
     * Actualiza un registro existente de AfipMapucheSicoss.
     */
    public function update(AfipMapucheSicoss $model, AfipMapucheSicossDTO $dto): AfipMapucheSicoss;

    /**
     * Elimina un registro de AfipMapucheSicoss.
     */
    public function delete(AfipMapucheSicoss $model): bool;

    /**
     * Trunca la tabla AfipMapucheSicoss.
     */
    public function truncate(): void;

    /**
     * Obtiene los períodos fiscales únicos de la tabla AfipMapucheSicoss.
     */
    public function getDistinctPeriodosFiscales(): array;
}
