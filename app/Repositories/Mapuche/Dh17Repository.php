<?php

declare(strict_types=1);

namespace App\Repositories\Mapuche;

use App\Data\Mapuche\Dh17Data;
use App\Models\Mapuche\Dh17;
use Illuminate\Database\Eloquent\Collection;

class Dh17Repository
{
    public function __construct(
        private readonly Dh17 $model,
    ) {
    }

    public function find(int $id): ?Dh17
    {
        return $this->model->find($id);
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function create(Dh17Data $data): Dh17
    {
        return $this->model->create($data->toArray());
    }

    public function update(int $id, Dh17Data $data): bool
    {
        return $this->model->where('codn_conce', $id)
            ->update($data->toArray());
    }

    public function delete(int $id): bool
    {
        return $this->model->where('codn_conce', $id)->delete();
    }
}
