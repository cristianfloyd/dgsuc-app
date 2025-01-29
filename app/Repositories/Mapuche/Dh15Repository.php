<?php

namespace App\Repositories\Mapuche;

use App\Models\Mapuche\Dh15;
use Illuminate\Database\Eloquent\Collection;

class Dh15Repository
{
    public function findById(int $id): ?Dh15
    {
        return Dh15::find($id);
    }

    public function getByTipoGrupo(int $tipoGrupo): Collection
    {
        return Dh15::porTipoGrupo($tipoGrupo)->get();
    }

    public function create(array $data): Dh15
    {
        return Dh15::create($data);
    }
}
