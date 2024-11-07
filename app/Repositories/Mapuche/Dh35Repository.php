<?php

namespace App\Repositories\Mapuche;

use App\Models\Mapuche\Dh35;
use App\Data\Mapuche\Dh35Data;
use Illuminate\Database\Eloquent\Collection;

class Dh35Repository
{
    public function __construct(
        private readonly Dh35 $model
    ) {}

    public function find(string $tipoEscal, string $codcCarac): ?Dh35
    {
        return $this->model->find([$tipoEscal, $codcCarac]);
    }

    public function create(Dh35Data $data): Dh35
    {
        return $this->model->create($data->toArray());
    }

    public function update(string $tipoEscal, string $codcCarac, Dh35Data $data): bool
    {
        return $this->model
            ->where('tipo_escal', $tipoEscal)
            ->where('codc_carac', $codcCarac)
            ->update($data->toArray());
    }

    public function getByTipoEscalafon(string $tipoEscal): Collection
    {
        return $this->model
            ->tipoEscalafon($tipoEscal)
            ->ordenadoPorOrden()
            ->get();
    }
}
