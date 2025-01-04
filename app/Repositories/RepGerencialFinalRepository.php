<?php

namespace App\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Reportes\RepGerencialFinal;
use App\Contracts\Mapuche\Repositories\RepGerencialFinalRepositoryInterface;

class RepGerencialFinalRepository implements RepGerencialFinalRepositoryInterface
{
    public function getByLiquidaciones(array $liquidaciones): Collection
    {
        return RepGerencialFinal::whereIn('nro_liqui', $liquidaciones)
            ->select([
                'nro_legaj',
                'desc_apyno',
                'coddependesemp',
                'imp_bruto',
                'imp_neto',
                'imp_dctos',
                'nro_liqui'
            ])
            ->orderBy('desc_apyno')
            ->get();
    }

    public function getTotalesPorInciso(array $liquidaciones): Collection
    {
        return RepGerencialFinal::whereIn('nro_liqui', $liquidaciones)
            ->select([
                'nro_inciso',
                DB::raw('SUM(imp_bruto) as total_bruto'),
                DB::raw('SUM(imp_neto) as total_neto'),
                DB::raw('SUM(imp_dctos) as total_descuentos'),
                DB::raw('COUNT(DISTINCT nro_legaj) as cantidad_agentes')
            ])
            ->groupBy('nro_inciso')
            ->orderBy('nro_inciso')
            ->get();
    }

    public function getTotalesPorDependencia(array $liquidaciones): Collection
    {
        return RepGerencialFinal::whereIn('nro_liqui', $liquidaciones)
            ->select([
                'coddependesemp',
                DB::raw('SUM(imp_bruto) as total_bruto'),
                DB::raw('SUM(imp_neto) as total_neto'),
                DB::raw('COUNT(DISTINCT nro_legaj) as cantidad_agentes')
            ])
            ->groupBy('coddependesemp')
            ->orderBy('coddependesemp')
            ->get();
    }

    public function getTotalesPorEscalafon(array $liquidaciones): Collection
    {
        return RepGerencialFinal::whereIn('nro_liqui', $liquidaciones)
            ->select([
                'tipo_escal',
                DB::raw('SUM(imp_bruto) as total_bruto'),
                DB::raw('SUM(imp_neto) as total_neto'),
                DB::raw('COUNT(DISTINCT nro_legaj) as cantidad_agentes')
            ])
            ->groupBy('tipo_escal')
            ->orderBy('tipo_escal')
            ->get();
    }

    public function getResumenPorAgrupamiento(array $liquidaciones): Collection
    {
        return RepGerencialFinal::whereIn('nro_liqui', $liquidaciones)
            ->select([
                'codc_agrup',
                DB::raw('SUM(imp_bruto) as total_bruto'),
                DB::raw('SUM(imp_neto) as total_neto'),
                DB::raw('COUNT(DISTINCT nro_legaj) as cantidad_agentes'),
                DB::raw('AVG(imp_bruto) as promedio_bruto')
            ])
            ->groupBy('codc_agrup')
            ->orderBy('codc_agrup')
            ->get();
    }
}
