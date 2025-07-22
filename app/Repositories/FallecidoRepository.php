<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Data\Reportes\FallecidoData;
use App\Models\RepFallecido;
use App\Repositories\Interfaces\FallecidoRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class FallecidoRepository implements FallecidoRepositoryInterface
{
    public function __construct(
        private readonly RepFallecido $model,
    ) {
    }

    public function all(): Collection
    {
        return $this->model->all()->map(fn ($fallecido) => $fallecido->toData());
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage)
            ->through(fn ($fallecido) => $fallecido->toData());
    }

    public function findByLegajo(int $nroLegajo): ?FallecidoData
    {
        $fallecido = $this->model->where('nro_legaj', $nroLegajo)->first();
        return $fallecido?->toData();
    }

    public function create(FallecidoData $data): FallecidoData
    {
        $fallecido = $this->model->create($data->toArray());
        return $fallecido->toData();
    }

    public function update(int $nroLegajo, FallecidoData $data): ?FallecidoData
    {
        $fallecido = $this->model->where('nro_legaj', $nroLegajo)->first();

        if (!$fallecido) {
            return null;
        }

        $fallecido->update($data->toArray());
        return $fallecido->fresh()->toData();
    }

    public function delete(int $nroLegajo): bool
    {
        return $this->model->where('nro_legaj', $nroLegajo)->delete() > 0;
    }
}
