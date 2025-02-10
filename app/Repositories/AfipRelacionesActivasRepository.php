<?php

namespace App\Repositories;

use App\Models\AfipRelacionesActivas;

class AfipRelacionesActivasRepository implements AfipRelacionesActivasRepositoryInterface
{
    public function findByCuil(string $cuil)
    {
        return AfipRelacionesActivas::byCuil($cuil)->first();
    }
}
