<?php

namespace App\Repositories;

use App\Contracts\Repositories\Dhr2RepositoryInterface;
use App\Data\DataObjects\Dhr2Data;
use App\Models\Mapuche\Dhr1;
use App\Models\Mapuche\Dhr2;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class Dhr2Repository implements Dhr2RepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected Dhr2 $model)
    {
    }

    public function findByPrimaryKey(int $nro_liqui, int $nro_legaj, int $nro_cargo): ?Dhr2Data
    {
        $record = $this->model->where('nro_liqui', $nro_liqui)
            ->where('nro_legaj', $nro_legaj)
            ->where('nro_cargo', $nro_cargo)
            ->first();
        return $record ? Dhr2Data::from($record) : null;
    }

    public function findByLiquidacionWithDetails(int $nro_liqui): Collection
    {
        return $this->model->where('nro_liqui', $nro_liqui)
            ->with('liquidacion')
            ->where('nro_liqui', $nro_liqui)
            ->get()
            ->map(fn ($record) => Dhr2Data::from($record));
    }

    /**
     * @inheritDoc
     */
    public function create(Dhr2Data $data): Dhr2Data
    {
        if (!$this->validateLiquidacionExists($data->nro_liqui)) {
            throw new \InvalidArgumentException('La liquidación principal no existe' . $data->nro_liqui);
        }

        $record = $this->model->create($data->toArray());
        return Dhr2Data::from($record);
    }

    public function validateLiquidacionExists(int $nro_liqui): bool
    {
        return Dhr1::where('nro_liqui', $nro_liqui)->exists();
    }

    /**
     * @inheritDoc
     */
    public function delete(int $nro_liqui, int $nro_legaj, int $nro_cargo): bool
    {
        return $this->model->where('nro_liqui', $nro_liqui)
            ->where('nro_legaj', $nro_legaj)
            ->where('nro_cargo', $nro_cargo)
            ->delete();
    }

    /**
     * @inheritDoc
     */
    public function findByLegajo(int $nro_legaj): Collection
    {
        return $this->model->where('nro_legaj', $nro_legaj)
            ->get()
            ->map(fn ($record) => Dhr2Data::from($record));
    }

    /**
     * @inheritDoc
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function update(Dhr2Data $data): bool
    {
        return $this->model
            ->where('nro_liqui', $data->nro_liqui)
            ->where('nro_legaj', $data->nro_legaj)
            ->where('nro_cargo', $data->nro_cargo)
            ->update($data->toArray());
    }
}
