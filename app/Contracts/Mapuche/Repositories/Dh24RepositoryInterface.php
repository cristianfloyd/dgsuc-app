<?php

namespace App\Contracts\Mapuche\Repositories;

use App\Data\Mapuche\Dh24Data;
use App\Models\Mapuche\Dh24;
use Illuminate\Database\Eloquent\Collection;

interface Dh24RepositoryInterface
{
    public function findByCargo(int $nroCargo): Collection;

    public function create(Dh24Data $data): Dh24;

    public function update(Dh24 $dh24, Dh24Data $data): bool;

    public function delete(Dh24 $dh24): bool;
}
