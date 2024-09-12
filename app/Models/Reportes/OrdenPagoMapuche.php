<?php

namespace App\Models\Reportes;

use App\Models\Dh21;
use App\Repositories\Interfaces\RepOrdenPagoRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Traits\MapucheDesaConnectionTrait;
use Illuminate\Database\Eloquent\Model;

class OrdenPagoMapuche extends Model
{
    use MapucheDesaConnectionTrait;

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
