<?php

namespace App\Services\Reportes;

use App\Models\Mapuche\Dh22;
use App\Models\Mapuche\Dh21h;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Exceptions\LiquidacionNotFoundException;

class LegajosSinLiquidarService
{
    private const int MESES_ANALISIS = 3;

    public function __construct(
        private readonly Dh22 $dh22,
        private readonly Dh21h $dh21h,
        private readonly PeriodoFiscalService $periodoFiscalService
    ){}

    /**
     * Obtiene los legajos sin liquidar en los últimos meses
     * @throws LiquidacionNotFoundException
     */
    public function getLegajosSinLiquidar()
    {
        try {
            // Obtener liquidaciones de referencia
            $ultimasLiquidaciones = $this->getLiquidacionesDefinitivasPorMes(self::MESES_ANALISIS);
            $liquidacionCuartoMes = $this->getLiquidacionDefinitivaCuartoMes();

            if (!$liquidacionCuartoMes) {
                throw new LiquidacionNotFoundException("No se encontró liquidación para el cuarto mes");
            }

            // Construir query principal
            return $this->construirQueryLegajosSinLiquidar($liquidacionCuartoMes, $ultimasLiquidaciones);
        } catch (\Exception $e) {
            Log::error('Error al obtener legajos sin liquidar: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
    * Obtiene las liquidaciones definitivas para un número específico de meses
    *
    * @param int $meses Número de meses a consultar
    * @return Collection Colección de liquidaciones ordenadas por período
    */
    public function getLiquidacionesDefinitivasPorMes(int $meses): Collection
    {
        $periodoFiscal = $this->periodoFiscalService->getPeriodoFiscalFromDatabase();
        $periodoReferencia = $periodoFiscal['year'] . $periodoFiscal['month'];

        return $this->dh22
            ->definitivaCerrada()
            ->select('nro_liqui', 'per_liano', 'per_limes', 'desc_liqui')
            ->whereRaw('(per_liano * 100 + per_limes) <= ?', [$periodoReferencia])
            ->orderByDesc('per_liano')
            ->orderByDesc('per_limes')
            ->whereIn(DB::raw('(per_liano * 100 + per_limes)'), function($query) use ($periodoReferencia, $meses) {
                $query->select(DB::raw('MAX(per_liano * 100 + per_limes)'))
                      ->from('mapuche.dh22')
                      ->where('sino_cerra', 'C')
                      ->whereRaw('LOWER(desc_liqui) LIKE ?', ['%definitiva%'])
                      ->whereRaw('(per_liano * 100 + per_limes) <= ?', [$periodoReferencia])
                      ->groupBy(DB::raw('per_liano * 100 + per_limes'))
                      ->orderByRaw('MAX(per_liano * 100 + per_limes) DESC')
                      ->limit($meses);
            })
            ->get();
    }

    /**
     * Obtiene la liquidación definitiva del cuarto mes anterior
     */
    public function getLiquidacionDefinitivaCuartoMes(): ?Dh22
    {
        $cuartoMesAtras = Carbon::now()->subMonths(4);

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

    /**
     * Construye la query principal para obtener legajos sin liquidar
     */
    private function construirQueryLegajosSinLiquidar(Dh22 $liquidacionCuartoMes, Collection $ultimasLiquidaciones): Builder
    {
        return $this->dh21h->newQuery()
            ->select(['dh21h.nro_legaj', 'dh21h.nro_cargo', 'dh21h.codc_uacad'])
            ->where('nro_liqui', $liquidacionCuartoMes->nro_liqui)
            ->whereNotExists(function ($query) use ($ultimasLiquidaciones) {
                $query->select('nro_legaj')
                    ->from('dh21h as liq_recientes')
                    ->whereColumn('liq_recientes.nro_legaj', 'dh21h.nro_legaj')
                    ->whereIn('liq_recientes.nro_liqui', $ultimasLiquidaciones->pluck('nro_liqui'));
            });
    }
}
