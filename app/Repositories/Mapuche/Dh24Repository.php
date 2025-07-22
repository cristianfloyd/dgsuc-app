<?php

namespace App\Repositories\Mapuche;

use App\Contracts\Mapuche\Repositories\Dh24RepositoryInterface;
use App\Data\Mapuche\Dh24Data;
use App\Models\Mapuche\Dh24;
use Illuminate\Database\Eloquent\Collection;

class Dh24Repository implements Dh24RepositoryInterface
{
    public function findByCargo(int $nroCargo): Collection
    {
        return Dh24::where('nro_cargo', $nroCargo)->get();
    }

    public function create(Dh24Data $data): Dh24
    {
        return Dh24::create($data->toArray());
    }

    public function update(Dh24 $dh24, Dh24Data $data): bool
    {
        return $dh24->update($data->toArray());
    }

    public function delete(Dh24 $dh24): bool
    {
        return $dh24->delete();
    }
}
