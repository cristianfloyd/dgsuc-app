<?php

namespace App\Models\Reportes;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Traits\MapucheDesaConnectionTrait;
use App\Traits\MapucheLiquiConnectionTrait;
use App\Contracts\RepOrdenPagoRepositoryInterface;

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
