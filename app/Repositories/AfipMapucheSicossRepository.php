<?php

namespace App\Repositories;

use App\Contracts\AfipMapucheSicossRepositoryInterface;
use App\DTOs\AfipMapucheSicossDTO;
use App\Models\AfipMapucheSicoss;

class AfipMapucheSicossRepository implements AfipMapucheSicossRepositoryInterface
{
    public function findByPeriodoAndCuil(string $periodoFiscal, string $cuil): ?AfipMapucheSicoss
    {
        return AfipMapucheSicoss::query()->where('periodo_fiscal', $periodoFiscal)
            ->where('cuil', $cuil)
            ->first();
    }

    public function create(AfipMapucheSicossDTO $dto): AfipMapucheSicoss
    {
        return AfipMapucheSicoss::query()->create($dto->toArray());
    }

    public function update(AfipMapucheSicoss $model, AfipMapucheSicossDTO $dto): AfipMapucheSicoss
    {
        $model->fill($dto->toArray());
        $model->save();
        return $model;
    }

    public function delete(AfipMapucheSicoss $model): bool
    {
        return $model->delete();
    }

    /**
     * Truncar la tabla AfipMapucheSicoss.
     */
    public function truncate(): void
    {
        AfipMapucheSicoss::query()->truncate();
    }

    /**
     * Obtiene los períodos fiscales distintos para ser usados en un select.
     */
    public function getDistinctPeriodosFiscales(): array
    {
        return AfipMapucheSicoss::query()->distinct()
            ->orderBy('periodo_fiscal', 'desc')
            ->pluck('periodo_fiscal', 'periodo_fiscal')
            ->toArray();
    }
}
