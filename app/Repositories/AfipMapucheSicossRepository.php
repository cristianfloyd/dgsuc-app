<?php

namespace App\Repositories;

use App\Models\AfipMapucheSicoss;
use App\DTOs\AfipMapucheSicossDTO;
use App\Contracts\AfipMapucheSicossRepositoryInterface;



class AfipMapucheSicossRepository implements AfipMapucheSicossRepositoryInterface
{
    public function findByPeriodoAndCuil(string $periodoFiscal, string $cuil): ?AfipMapucheSicoss
    {
        return AfipMapucheSicoss::where('periodo_fiscal', $periodoFiscal)
            ->where('cuil', $cuil)
            ->first();
    }

    public function create(AfipMapucheSicossDTO $dto): AfipMapucheSicoss
    {
        return AfipMapucheSicoss::create($dto->toArray());
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
     *
     * @return void
     */
    public function truncate(): void
    {
        AfipMapucheSicoss::truncate();
    }

    /**
     * Obtiene los períodos fiscales distintos para ser usados en un select.
     *
     * @return array
     */
    public function getDistinctPeriodosFiscales(): array
    {
        return AfipMapucheSicoss::distinct()
            ->orderBy('periodo_fiscal', 'desc')
            ->pluck('periodo_fiscal', 'periodo_fiscal')
            ->toArray();
    }
}
