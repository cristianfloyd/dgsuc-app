<?php

namespace App\Contracts\Mapuche\Repositories;

use App\DTOs\Mapuche\Dh13\CreateDh13DTO;
use App\DTOs\Mapuche\Dh13\UpdateDh13DTO;
use App\Models\Dh13;
use Illuminate\Database\Eloquent\Collection;

interface Dh13RepositoryInterface
{
    public function findByConcepto(int $codn_conce): Collection;

    public function create(CreateDh13DTO $data): Dh13;

    public function update(UpdateDh13DTO $data, int $codn_conce, int $nro_orden_formula): bool;

    public function delete(int $codn_conce, int $nro_orden_formula): bool;
}
