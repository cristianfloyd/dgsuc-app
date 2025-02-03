<?php

namespace App\Services\Mapuche;

use Carbon\Carbon;
use App\Models\Dh03;
use App\Models\Dh21;
use App\Models\Mapuche\Dh21h;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\ValueObjects\PeriodoLiquidacion;

class DosubaReportService
{
    public function __construct(
        private readonly PeriodoFiscalService $periodoFiscalService,
    ) {}

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
        Log::debug('Fechas de referencia', [
            'fechaReferencia' => $fechaReferencia,
            'fechaInicio' => $fechaInicio,
            'fechaCuartoMes' => $fechaCuartoMes
        ]);

        try {
            // Obtener empleados del primer mes
            $empleadosPrimerMes = $this->legajosPrimerMes($fechaInicio, $fechaReferencia);

            // Obtener empleados del segundo mes
            $empleadosSegundoMes = $this->legajosSegundoMes($fechaInicio, $fechaReferencia);

            // Obtenemos empleados de los últimos 3 meses
            // $empleadosTresMeses = $this->legajosTercerMes($fechaInicio, $fechaReferencia);

            // Obtener empleados del cuarto mes
            // $empleadosCuartoMes = $this->legajosCuartoMes($fechaCuartoMes);

            // Realizamos el cruce de información
            return $this->cruzarLegajos($empleadosSegundoMes, $empleadosPrimerMes);
        } catch (\Exception $e) {
            Log::error('Error en DosubaReportService: ' . $e->getMessage());
            throw new \Exception('Error al generar el reporte DOSUBA');
        }
    }

    /**
     * Obtiene los legajos del primer mes
     * @param Carbon $fechaInicio
     * @param Carbon $fechaReferencia
     * @return mixed
     */
    public function legajosPrimerMes(Carbon $fechaInicio, Carbon $fechaReferencia): Collection
    {
        $query = Dh21h::query()
            ->join('mapuche.dh22', 'dh21h.nro_liqui', '=', 'dh22.nro_liqui')
            ->where('dh22.per_liano', $fechaInicio->year)
            ->where('dh22.per_limes', $fechaInicio->month)
            ->whereRaw("LOWER(dh22.desc_liqui) LIKE '%definitiva%'")
            ->select([
                'dh21h.nro_legaj',
                'dh21h.nro_liqui',
                'dh21h.codc_uacad',
                'dh22.per_liano as anio',
                'dh22.per_limes as mes',
            ])
            ->distinct();

        Log::info('SQL Query Primer Mes:', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        return $query->get();
    }

    /**
     * Obtiene los legajos del segundo mes
     * @param Carbon $fechaInicio
     * @param Carbon $fechaReferencia
     * @return mixed
     */
    public function legajosSegundoMes(Carbon $fechaInicio, Carbon $fechaReferencia): Collection
    {
        $query = Dh21h::query()
            ->join('mapuche.dh22', 'dh21h.nro_liqui', '=', 'dh22.nro_liqui')
            ->where('dh22.per_liano', $fechaReferencia->copy()->subMonths(2)->year)
            ->where('dh22.per_limes', $fechaReferencia->copy()->subMonths(2)->month)
            // ->whereRaw("LOWER(dh22.desc_liqui) LIKE '%definitiva%'")
            ->select([
                'dh21h.nro_legaj',
                'dh21h.nro_liqui',
                'dh21h.codc_uacad',
                'dh22.per_liano as anio',
                'dh22.per_limes as mes',
            ])
            ->distinct();

        Log::info('SQL Query Segundo Mes:', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        return $query->get();
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
            ->distinct();

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
    public function cruzarLegajos(mixed $empleadosSegundoMes, mixed $empleadosPrimerMes): Collection|\Illuminate\Database\Eloquent\Collection
    {
        // Convertimos las colecciones a arrays de legajos únicos
        $legajosSegundoMes = $empleadosSegundoMes->pluck('nro_legaj')->unique()->values()->toArray();
        $legajosPrimerMes = $empleadosPrimerMes->pluck('nro_legaj')->unique()->values()->toArray();

        // Encontrar legajos que están en el segundo mes (diciembre 2024) pero no en el primer mes (enero 2025)
        $legajosDiferencia = array_diff($legajosSegundoMes, $legajosPrimerMes);

        // Log para debugging
        Log::info('Análisis de legajos:', [
            'total_segundo_mes' => count($legajosSegundoMes),
            'total_primer_mes' => count($legajosPrimerMes),
            'diferencia_encontrada' => count($legajosDiferencia),
            'ejemplo_legajos_diferencia' => array_slice($legajosDiferencia, 0, 5) // Muestra los primeros 5 legajos de diferencia
        ]);

        $resultados = collect();
        $chunkSize = 1000;

        // Procesamos solo los legajos que están en la diferencia
        foreach (array_chunk($legajosDiferencia, $chunkSize) as $chunk) {
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
                    'dh22.per_limes as mes',
                    DB::raw('(EXISTS (
                        SELECT 1
                        FROM mapuche.dh21h as dh21h_embarazo
                        WHERE dh21h_embarazo.nro_legaj = dh03.nro_legaj
                        AND dh21h_embarazo.codn_conce = 126
                    )) as embarazada'),
                    DB::raw('(dh09.fec_defun IS NOT NULL) as fallecido')
                ])
                ->distinct()
                ->join('dh01', 'dh03.nro_legaj', '=', 'dh01.nro_legaj')
                ->join('dh21h', 'dh03.nro_legaj', '=', 'dh21h.nro_legaj')
                ->join('dh22', 'dh21h.nro_liqui', '=', 'dh22.nro_liqui')
                ->leftJoin('dh09', 'dh03.nro_legaj', '=', 'dh09.nro_legaj')
                ->whereIn('dh03.nro_legaj', $chunk)
                ->orderBy('dh03.nro_legaj');

            Log::info('Query chunk procesado:', [
                'chunk_size' => count($chunk),
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $resultados = $resultados->concat($query->get());
        }

        return $resultados->map(function ($item) {
            return [
                'nro_legaj' => $item->nro_legaj,
                'cuil' => $item->nro_cuil1 . $item->nro_cuil . $item->nro_cuil2,
                'apellido' => $item->apellido,
                'nombre' => $item->nombre,
                'ultima_liquidacion' => $item->nro_liqui,
                'codc_uacad' => $item->codc_uacad,
                'periodo_fiscal' => $item->anio . $item->mes,
                'anio' => $item->anio,
                'mes' => $item->mes,
                'embarazada' => $item->embarazada,
                'fallecido' => $item->fallecido
            ];
        });
    }
}
