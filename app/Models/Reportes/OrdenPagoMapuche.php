<?php

namespace App\Models\Reportes;

use App\Contracts\RepOrdenPagoRepositoryInterface;
use App\Traits\MapucheLiquiConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class OrdenPagoMapuche extends Model
{
    use MapucheLiquiConnectionTrait;

    public function __construct(protected \App\Contracts\RepOrdenPagoRepositoryInterface $repOrdenPagoRepository)
    {
    }

    public function getOrdenPago(): Collection
    {
        return $this->repOrdenPagoRepository->getAll();
    }
}
