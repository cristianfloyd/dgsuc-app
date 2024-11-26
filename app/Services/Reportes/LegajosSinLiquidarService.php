<?php

namespace App\Services\Reportes;

use App\Models\Mapuche\Dh22;
use App\Models\Mapuche\Dh21h;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LegajosSinLiquidarService
{

    public function __construct(
        private Dh22 $dh22,
        private Dh21h $dh21h
    ){}

    public function getLegajosSinLiquidar()
    {
        // Obtener liquidación definitiva de los últimos 3 meses (oct, sep, ago)
        $ultimasTresLiquidaciones = $this->getLiquidacionesDefinitivasPorMes(3);

        // Obtener liquidación definitiva del cuarto mes hacia atrás (julio)
        $liquidacionCuartoMes = $this->getLiquidacionDefinitivaCuartoMes();

        // Obtener legajos que fueron liquidados en julio
        return $this->dh21h
            ->whereIn('nro_liqui', [$liquidacionCuartoMes->nro_liqui])
            ->whereNotExists(function ($query) use ($ultimasTresLiquidaciones) {
                $query->select('nro_legaj')
                    ->from('dh21h as liq_recientes')
                    ->whereColumn('liq_recientes.nro_legaj', 'dh21h.nro_legaj')
                    ->whereIn('liq_recientes.nro_liqui', $ultimasTresLiquidaciones->pluck('nro_liqui'));
            });
    }


    public function getLiquidacionesDefinitivasPorMes(int $meses):Builder
    {
        return $this->dh22
            ->definitiva()
            ->select('nro_liqui', 'per_liano', 'per_limes')
            ->orderByDesc('per_liano')
            ->orderByDesc('per_limes')
            ->whereRaw('(per_liano * 100 + per_limes) <= ?', [now()->format('Ym')])
            ->groupBy('per_liano', 'per_limes')
            ->take($meses)
            ->get();
    }

    public function getLiquidacionDefinitivaCuartoMes(): ?Dh22
    {
        $cuartoMesAtras = now()->subMonths(4);

        return $this->dh22
            ->definitiva()
            ->where('per_liano', $cuartoMesAtras->year)
            ->where('per_limes', $cuartoMesAtras->month)
            ->first();
    }

    public function excluirLegajosLiquidados(Builder $query, Collection $liquidaciones)
    {
        $idsLiquidaciones = $liquidaciones->pluck('nro_liqui');

        return $query->whereNotExists(function ($subquery) use ($idsLiquidaciones) {
            $subquery->select('nro_legaj')
                    ->from('dh21h as liquidaciones')
                    ->whereColumn('liquidaciones.nro_legaj', 'dh21h.nro_legaj')
                    ->whereIn('liquidaciones.nro_liqui', $idsLiquidaciones);
        });
    }
}
