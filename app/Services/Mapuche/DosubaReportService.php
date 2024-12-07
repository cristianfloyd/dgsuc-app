<?php

namespace App\Services\Mapuche;

use Carbon\Carbon;
use App\Models\Dh03;
use App\Models\Mapuche\Dh21h;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\ValueObjects\PeriodoLiquidacion;

class DosubaReportService
{
    public function __construct(
        private readonly PeriodoFiscalService $periodoFiscalService,
    )
    {
    }

    /**
     * Obtiene el reporte DOSUBA para un período específico
     * @param string $year
     * @param string $month
     * @return Collection
     */
    public function getDosubaReport(string $year = null, string $month = null): Collection
    {
        if ($year === null || $month === null) {
            $year = $this->periodoFiscalService->getYear();
            $month = $this->periodoFiscalService->getMonth();
        }

        $periodo = new PeriodoLiquidacion($year, $month);


        // Calculamos las fechas de referencia
        $fechaReferencia = $periodo->getFechaReferencia();
        $fechaInicio = $periodo->getFechaInicio();
        $fechaCuartoMes = $periodo->getFechaCuartoMes();

        try {
            // Obtener empleados del cuarto mes
            $empleadosCuartoMes = $this->legajosCuartoMes($fechaCuartoMes);

            // Obtenemos empleados de los últimos 3 meses
            $empleadosTresMeses = $this->legajosTercerMes($fechaInicio, $fechaReferencia);

            // Realizamos el cruce de información
            return $this->cruzarLegajos($empleadosCuartoMes, $empleadosTresMeses);
        } catch (\Exception $e) {
            Log::error('Error en DosubaReportService: ' . $e->getMessage());
            throw new \Exception('Error al generar el reporte DOSUBA');
        }
    }

    /**
     * @param Carbon $fechaCuartoMes
     * @return mixed
     */
    public function legajosCuartoMes(Carbon $fechaCuartoMes): mixed
    {
        $query = Dh21h::query()
            ->join('dh22', 'dh21h.nro_liqui', '=', 'dh22.nro_liqui')
            ->where('dh22.per_liano', $fechaCuartoMes->year)
            ->where('dh22.per_limes', $fechaCuartoMes->month)
            ->whereRaw("LOWER(dh22.desc_liqui) LIKE '%definitiva%'")
            ->select([
                'dh21h.nro_legaj',
                'dh21h.nro_liqui',
                'dh21h.codc_uacad',
                'dh22.per_liano as anio',
                'dh22.per_limes as mes',
                ])
            ->distinct();

        Log::info('SQL Query:', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        return $query->get();
    }

    /**
     * @param Carbon $fechaInicio
     * @param Carbon $fechaReferencia
     * @return mixed
     */
    public function legajosTercerMes(Carbon $fechaInicio, Carbon $fechaReferencia)
    {
        $query = Dh21h::query()
            ->entreFechas($fechaInicio, $fechaReferencia)
            ->select('nro_legaj')
            ->distinct()
            ;

        Log::info('SQL Query:', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        return $query->get();
    }

    /**
     * @param mixed $empleadosCuartoMes
     * @param mixed $empleadosTresMeses
     * @return \Illuminate\Database\Eloquent\Collection|Collection
     */
    public function cruzarLegajos(mixed $empleadosCuartoMes, mixed $empleadosTresMeses): Collection|\Illuminate\Database\Eloquent\Collection
    {
        $query = Dh03::query()
        ->select([
            'dh03.nro_legaj',
            'dh01.nro_cuil1',
            'dh01.nro_cuil',
            'dh01.nro_cuil2',
            'dh01.desc_appat as apellido',
            'dh01.desc_nombr as nombre',
            'dh21h.nro_liqui',
            'dh21h.codc_uacad',
            'dh22.per_liano as anio',
            'dh22.per_limes as mes'
        ])
        ->distinct()
        ->join('dh01', 'dh03.nro_legaj', '=', 'dh01.nro_legaj')
        ->join('dh21h', 'dh03.nro_legaj', '=', 'dh21h.nro_legaj')
        ->join('dh22', 'dh21h.nro_liqui', '=', 'dh22.nro_liqui')
        ->whereIn('dh03.nro_legaj', $empleadosCuartoMes->pluck('nro_legaj'))
        ->whereNotIn('dh03.nro_legaj', $empleadosTresMeses->pluck('nro_legaj'))
        ->orderBy('dh03.nro_legaj');

        // Debug de la consulta
        Log::info('Query cruzarLegajos:', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        return $query->get()->map(function($item) {
            return [
                'nro_legaj' => $item->nro_legaj,
                'cuil' => $item->nro_cuil1 . $item->nro_cuil . $item->nro_cuil2,
                'apellido' => $item->apellido,
                'nombre' => $item->nombre,
                'ultima_liquidacion' => $item->nro_liqui,
                'codc_uacad' => $item->codc_uacad,
                'periodo_fiscal' => $item->anio . $item->mes,
                'anio' => $item->anio,
                'mes' => $item->mes
            ];
        });
    }
}
