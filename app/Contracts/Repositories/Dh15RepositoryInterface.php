<?php

namespace App\Contracts\Repositories;

use App\Models\Mapuche\Dh15;
use Illuminate\Database\Eloquent\Collection;

interface Dh15RepositoryInterface
{
    public function findById(int $id): ?Dh15;

    public function getByTipoGrupo(int $tipoGrupo): Collection;

    public function create(array $data): Dh15;
}
