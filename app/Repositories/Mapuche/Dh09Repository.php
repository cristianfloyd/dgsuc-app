<?php

declare(strict_types=1);

namespace App\Repositories\Mapuche;

use App\Data\Mapuche\Dh09Data;
use App\Models\Mapuche\Dh09;
use Illuminate\Database\Eloquent\Collection;

class Dh09Repository
{
    public function __construct(private Dh09 $model)
    {
    }

    public function findByLegajo(int $nroLegajo): ?Dh09Data
    {
        $record = $this->model->find($nroLegajo);
        return $record ? Dh09Data::from($record) : null;
    }

    public function getActivosByUnidadAcademica(string $codcUacad): Collection
    {
        return $this->model
            ->where('codc_uacad', $codcUacad)
            ->whereNull('fec_defun')
            ->whereNull('fecha_jubilacion')
            ->get();
    }
}
