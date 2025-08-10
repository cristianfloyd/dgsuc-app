<?php

namespace App\Repositories\Mapuche;

use App\Models\Mapuche\Dha8;

class Dha8Repository
{
    public function __construct(private Dha8 $model)
    {
    }

    public function findByLegajo(int $nroLegajo): ?Dha8
    {
        return $this->model->find($nroLegajo);
    }
}
