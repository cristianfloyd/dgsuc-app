<?php

namespace App\Services\Mapuche;

use App\Models\Dh03;
use App\Models\Mapuche\Dh21h;
use App\ValueObjects\PeriodoLiquidacion;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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
        return Dh21h::query()
            ->whereYear('dh22.per_liano', $fechaCuartoMes->year)
            ->whereMonth('dh22.per_limes', $fechaCuartoMes->month)
            ->definitiva()
            ->select('nro_legaj')
            ->distinct()
            ->get();
    }

    /**
     * @param Carbon $fechaInicio
     * @param Carbon $fechaReferencia
     * @return mixed
     */
    public function legajosTercerMes(Carbon $fechaInicio, Carbon $fechaReferencia)
    {
        return Dh21h::query()
            ->entreFechas($fechaInicio, $fechaReferencia)
            ->empleadosActivos()
            ->select('cuil')
            ->distinct()
            ->get();
    }

    /**
     * @param mixed $empleadosCuartoMes
     * @param mixed $empleadosTresMeses
     * @return \Illuminate\Database\Eloquent\Collection|Collection
     */
    public function cruzarLegajos(mixed $empleadosCuartoMes, mixed $empleadosTresMeses): Collection|\Illuminate\Database\Eloquent\Collection
    {
        return Dh03::query()
            ->whereIn('cuil', $empleadosCuartoMes->pluck('cuil'))
            ->whereNotIn('cuil', $empleadosTresMeses->pluck('cuil'))
            ->with(['persona' => function ($query) {
                $query->select('cuil', 'apellido', 'nombre');
            }])
            ->select('id_legajo', 'cuil')
            ->orderBy('id_legajo')
            ->get()
            ->map(function ($empleado) {
                return [
                    'IdLegajo' => $empleado->id_legajo,
                    'CUIL' => $empleado->cuil,
                    'Apellido' => $empleado->persona->apellido,
                    'Nombre' => $empleado->persona->nombre
                ];
            });
    }
}
