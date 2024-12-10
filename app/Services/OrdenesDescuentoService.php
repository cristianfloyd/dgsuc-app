<?php

namespace App\Services;

use App\Traits\MapucheConnectionTrait;
use App\Models\Reportes\OrdenesDescuento;
use Illuminate\Database\Eloquent\Builder;

class OrdenesDescuentoService
{
    use MapucheConnectionTrait;

    public function getBaseQuery(): Builder
    {
        return OrdenesDescuento::query();
    }

    public function getReporteOrdenesDescuento(string $periodoFiscal, ?int $nroLiqui = null): Builder
    {
        return $this->getBaseQuery()
            ->when($periodoFiscal, fn($q) => $q->periodo($periodoFiscal))
            ->when($nroLiqui, fn($q) => $q->porLiquidacion($nroLiqui))
            ->orderBy('codc_uacad')
            ->orderBy('descripcion_dep_pago');
    }
}

