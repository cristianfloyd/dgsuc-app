<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\AfipMapucheSicoss;
use Illuminate\Support\Facades\DB;
use App\Traits\DynamicConnectionTrait;
use App\Traits\MapucheConnectionTrait;
use App\Models\AfipMapucheSicossCalculo;
use App\Data\AfipMapucheSicossCalculoData;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Contracts\AfipMapucheSicossCalculoRepository;

class EloquentAfipMapucheSicossCalculoRepository implements AfipMapucheSicossCalculoRepository
{
    use DynamicConnectionTrait;

    public function __construct(
        private readonly AfipMapucheSicossCalculo $model
    ) {}

    /**
     * Obtiene la conexión actual para las operaciones del repositorio
     */
    private function getConnection()
    {
        return $this->getConnectionFromTrait();
    }

    public function find(string $cuil): ?AfipMapucheSicossCalculoData
    {
        $model = $this->model->where('cuil', $cuil)->first();
        return $model ? $model->toData() : null;
    }

    public function create(AfipMapucheSicossCalculoData $data): AfipMapucheSicossCalculoData
    {
        $model = $this->model->create($data->toArray());
        return $model->toData();
    }

    public function update(string $cuil, AfipMapucheSicossCalculoData $data): bool
    {
        return $this->model->where('cuil', $cuil)->update($data->toArray());
    }

    public function delete(string $cuil): bool
    {
        return $this->model->where('cuil', $cuil)->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function truncate(): void
    {
        $this->getConnection()->table($this->model->getTable())->truncate();
    }
}
