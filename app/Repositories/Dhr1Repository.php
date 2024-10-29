<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Mapuche\Dhr1;
use App\Data\DataObjects\Dhr1Data;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\Repositories\Dhr1RepositoryInterface;

class Dhr1Repository implements Dhr1RepositoryInterface
{
    protected $property;

  public function __construct($property){}

  public function find(int $nro_liqui): ?Dhr1
    {
        return $this->model->find($nro_liqui);
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function create(Dhr1Data $data): Dhr1
    {
        return $this->model->create($data->toArray());
    }

    public function update(int $nro_liqui, Dhr1Data $data): bool
    {
        return $this->model->find($nro_liqui)?->update($data->toArray()) ?? false;
    }

    public function delete(int $nro_liqui): bool
    {
        return $this->model->find($nro_liqui)?->delete() ?? false;
    }
}
