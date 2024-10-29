<?php

namespace App\Repositories;

use App\Models\Mapuche\Dhr2;
use App\Data\DataObjects\Dhr2Data;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Contracts\Repositories\Dhr2RepositoryInterface;

class Dhr2Repository implements Dhr2RepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(private Dhr2Data $model){}

    public function findByPrimaryKey(int $nro_liqui, int $nro_legaj, int $nro_cargo): ?Dhr2Data
    {
        $record = $this->model->where('nro_liqui', $nro_liqui)
            ->where('nro_legaj', $nro_legaj)
            ->where('nro_cargo', $nro_cargo)
            ->first();
        return $record ? Dhr2Data::from($record) : null;
    }

    /**
     * @inheritDoc
     */
    public function create(Dhr2Data $data): Dhr2Data
    {
        $record = $this->model->create($data->toArray());
        return Dhr2Data::from($record);
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
            ->map(fn($record) => Dhr2Data::from($record));
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
    public function update(int $nro_liqui, int $nro_legaj, int $nro_cargo, Dhr2Data $data): bool
    {
        return $this->model->where('nro_liqui', $nro_liqui)
            ->where('nro_legaj', $nro_legaj)
            ->where('nro_cargo', $nro_cargo)
            ->update($data->toArray());
    }
}
