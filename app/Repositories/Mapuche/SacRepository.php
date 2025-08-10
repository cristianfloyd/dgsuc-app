<?php

declare(strict_types=1);

namespace App\Repositories\Mapuche;

use App\Data\Mapuche\SacCargoData;
use App\Models\Mapuche\{Dh10};
use App\Services\Mapuche\PeriodoFiscalService;
use Illuminate\Support\Collection;

class SacRepository
{
    public function __construct(
        private PeriodoFiscalService $periodoService,
    ) {
    }

    /**
     * Obtiene los datos de brutos SAC para un cargo específico.
     */
    public function getBrutosSacCargo(int $legajo, int $nroCargo): ?SacCargoData
    {
        $periodo = $this->periodoService->getPeriodoActual();

        return Dh10::with(['cargoVinculado'])
            ->whereHas('cargo.empleado', fn ($q) => $q->where('nro_legaj', $legajo))
            ->where('nro_cargo', $nroCargo)
            ->first()
            ?->pipe(fn ($model) => SacCargoData::from($model));
    }

    /**
     * Obtiene todos los cargos SAC con filtros aplicados.
     */
    public function getBrutosParaSac(array $filtros, string $orderBy = ''): Collection
    {
        $query = Dh10::query()
            ->join('dh03', 'dh10.nro_cargo', '=', 'dh03.nro_cargo')
            ->join('dh01', 'dh01.nro_legaj', '=', 'dh03.nro_legaj')
            ->select([
                'dh03.nro_legaj',
                'dh03.fec_baja',
                'dh03.fec_alta',
                'dh03.codc_categ',
                'dh03.codc_uacad',
                'dh01.tipo_docum',
                'dh01.nro_docum',
                'dh01.desc_appat',
                'dh01.desc_nombr',
                'dh10.*',
            ]);

        $this->aplicarFiltros($query, $filtros);
        $this->aplicarOrdenamiento($query, $orderBy);

        return $query->get();
    }

    /**
     * Actualiza los importes de un cargo SAC.
     */
    public function actualizarCargo(int $nroCargo, array $datos): bool
    {
        return Dh10::where('nro_cargo', $nroCargo)->update($datos);
    }

    /**
     * Crea un nuevo registro SAC para un cargo.
     */
    public function crearCargoSac(int $nroCargo): array
    {
        $datos = [
            'nro_cargo' => $nroCargo,
            'vcl_cargo' => $nroCargo,
        ];

        for ($i = 1; $i <= 12; $i++) {
            $datos["imp_bruto_{$i}"] = 0;
        }

        return $datos;
    }

    private function aplicarFiltros($query, array $filtros): void
    {
        if (isset($filtros['cod_regional'])) {
            $operador = $filtros['cod_regional']['condicion'] === 'es_igual_a' ? '=' : '!=';
            $query->where('dh03.codc_regio', $operador, $filtros['cod_regional']['valor']);
        }

        if (isset($filtros['cod_depcia'])) {
            $operador = $filtros['cod_depcia']['condicion'] === 'es_igual_a' ? '=' : '!=';
            $query->where('dh03.codc_uacad', $operador, $filtros['cod_depcia']['valor']);
        }

        if (isset($filtros['periodo'])) {
            $this->aplicarFiltroPeriodo($query, $filtros['periodo']);
        }
    }

    private function aplicarFiltroPeriodo($query, array $filtroPeriodo): void
    {
        $periodo = $this->periodoService->calcularPeriodoSac($filtroPeriodo);

        $query->where(function ($q) use ($periodo): void {
            $q->where(function ($subQ) use ($periodo): void {
                $subQ->where('dh03.fec_alta', '<=', $periodo['fecha_inicio'])
                    ->where(function ($dateQ) use ($periodo): void {
                        $dateQ->where('dh03.fec_baja', '>=', $periodo['fecha_fin'])
                            ->orWhereNull('dh03.fec_baja');
                    });
            });
            // Agregar más condiciones de período según la lógica original
        });
    }

    private function aplicarOrdenamiento($query, string $orderBy): void
    {
        if (!empty($orderBy)) {
            $query->orderByRaw("desc_appat || ', ' || desc_nombr {$orderBy}");
        } else {
            $query->orderBy('dh03.codc_uacad')
                ->orderByRaw("desc_appat || ', ' || desc_nombr")
                ->orderBy('dh03.nro_legaj')
                ->orderBy('dh03.fec_baja', 'desc');
        }
    }
}
