<?php

namespace App\Models\Reportes;

use App\Contracts\RepOrdenPagoRepositoryInterface;
use App\Traits\MapucheLiquiConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class OrdenPagoMapuche extends Model
{
    use MapucheLiquiConnectionTrait;

    protected $repOrdenPagoRepository;

    public function __construct(RepOrdenPagoRepositoryInterface $repOrdenPagoRepository)
    {
        $this->repOrdenPagoRepository = $repOrdenPagoRepository;
    }

    public function getOrdenPago(): Collection
    {
        return $this->repOrdenPagoRepository->getAll();
    }
}
