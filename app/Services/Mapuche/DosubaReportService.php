<?php

namespace App\Services\Mapuche;

use App\Models\Dh03;
use App\Models\Mapuche\Dh21h;
use App\Traits\MapucheConnectionTrait;
use App\ValueObjects\PeriodoLiquidacion;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DosubaReportService
{
    use MapucheConnectionTrait;

    /**
     * Constructor del servicio.
     *
     * @param PeriodoFiscalService $periodoFiscalService Servicio para obtener información del período fiscal
     */
    public function __construct(
        private readonly PeriodoFiscalService $periodoFiscalService,
    ) {
    }

    /**
     * Obtiene el reporte DOSUBA para un período específico.
     *
     * Este método identifica legajos que estaban activos en un período anterior
     * pero que ya no aparecen en los períodos más recientes, lo que podría indicar
     * bajas o cambios en la situación laboral.
     *
     * @param string|null $year Año del período fiscal (formato: YYYY)
     * @param string|null $month Mes del período fiscal (formato: MM)
     *
     * @throws \Exception Si ocurre un error durante la generación del reporte
     *
     * @return Collection Colección con los datos del reporte
     */
    public function getDosubaReport(?string $year = null, ?string $month = null): Collection
    {
        try {
            // Inicialización de fechas
            $periodo = $this->inicializarPeriodo($year, $month);
            $fechas = $this->calcularFechasReferencia($periodo);

            // Obtención y procesamiento de legajos
            $legajosPorMes = $this->obtenerLegajosPorMes($fechas);
            $legajosCombinados = $this->combinarLegajosPrimerYSegundoMes($legajosPorMes);



            // Realizamos el cruce de información entre los legajos combinados y los del tercer mes
            return $this->cruzarLegajos($legajosPorMes['tercerMes'], $legajosCombinados);
        } catch (\Exception $e) {
            Log::error('Error en DosubaReportService: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'year' => $year,
                'month' => $month,
            ]);
            throw new \Exception('Error al generar el reporte DOSUBA: ' . $e->getMessage());
        }
    }

    /**
     * Método unificado para obtener legajos de un mes específico.
     *
     * @param Carbon $fecha Fecha del mes para obtener los legajos
     * @param bool $soloDefinitivas Si true, filtra solo liquidaciones definitivas
     *
     * @return Collection
     */
    public function legajosMes(Carbon $fecha, bool $soloDefinitivas = true): Collection
    {
        $query = Dh21h::query()
            ->join('mapuche.dh22', 'dh21h.nro_liqui', '=', 'dh22.nro_liqui')
            ->where('dh22.per_liano', $fecha->year)
            ->where('dh22.per_limes', $fecha->month)
            ->select([
                'dh21h.nro_legaj',
                'dh21h.nro_liqui',
                'dh21h.codc_uacad',
                'dh22.per_liano as anio',
                'dh22.per_limes as mes',
            ])
            ->distinct();

        // Aplicamos el filtro de liquidaciones definitivas si es necesario
        if ($soloDefinitivas) {
            $query->whereRaw("LOWER(dh22.desc_liqui) LIKE '%definitiva%'");
        }

        Log::info('SQL Query legajosMes:', [
            'fecha' => $fecha->format('Y-m'),
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
        ]);

        return $query->get();
    }

    /**
     * Cruza los legajos entre dos colecciones para encontrar diferencias.
     *
     * @param Collection $legajosTercerMes Legajos del tercer mes
     * @param Collection $legajosCombinados Legajos combinados del primer y segundo mes
     *
     * @return Collection
     */
    public function cruzarLegajos(Collection $legajosTercerMes, Collection $legajosCombinados): Collection
    {
        // Convertimos las colecciones a arrays de legajos únicos
        $legajosTercerMesArray = $legajosTercerMes->pluck('nro_legaj')->unique()->values()->toArray();
        $legajosCombinados = $legajosCombinados->pluck('nro_legaj')->unique()->values()->toArray();

        // Encontramos los legajos que están en el tercer mes pero no en los meses combinados
        $legajosDiferencia = array_diff($legajosTercerMesArray, $legajosCombinados);

        // Log para debugging
        Log::info('Análisis de legajos:', [
            'total_tercer_mes' => \count($legajosTercerMesArray),
            'total_combinados' => \count($legajosCombinados),
            'diferencia_encontrada' => \count($legajosDiferencia),
            'ejemplo_legajos_diferencia' => \array_slice($legajosDiferencia, 0, 5), // Muestra los primeros 5 legajos de diferencia
        ]);

        $resultados = collect();
        $chunkSize = 1000;

        // Obtenemos el año y mes del tercer mes para filtrar
        $fechaTercerMes = $legajosTercerMes->first();
        $anioTercerMes = $fechaTercerMes ? $fechaTercerMes->anio : null;
        $mesTercerMes = $fechaTercerMes ? $fechaTercerMes->mes : null;

        if (!$anioTercerMes || !$mesTercerMes) {
            Log::error('No se pudo determinar el período del tercer mes');
            return collect();
        }


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
                    DB::raw('(dh09.fec_defun IS NOT NULL) as fallecido'),
                ])
                ->distinct()
                ->join('dh01', 'dh03.nro_legaj', '=', 'dh01.nro_legaj')
                ->join('dh21h', 'dh03.nro_legaj', '=', 'dh21h.nro_legaj')
                ->join('dh22', 'dh21h.nro_liqui', '=', 'dh22.nro_liqui')
                ->leftJoin('dh09', 'dh03.nro_legaj', '=', 'dh09.nro_legaj')
                ->whereIn('dh03.nro_legaj', $chunk)
                ->where('dh22.per_liano', $anioTercerMes)
                ->where('dh22.per_limes', $mesTercerMes)
                ->orderBy('dh03.nro_legaj');

            Log::info('Query chunk procesado:', [
                'chunk_size' => \count($chunk),
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
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
                'fallecido' => $item->fallecido,
            ];
        });
    }

    /**
     * Inicializa el objeto PeriodoLiquidacion con el año y mes proporcionados.
     *
     * @param string|null $year Año del período
     * @param string|null $month Mes del período
     *
     * @return PeriodoLiquidacion Objeto con el período inicializado
     */
    private function inicializarPeriodo(?string $year, ?string $month): PeriodoLiquidacion
    {
        // Si no se especifica año o mes, usamos el período fiscal actual
        if ($year === null || $month === null) {
            $year = $this->periodoFiscalService->getYear();
            $month = $this->periodoFiscalService->getMonth();
        }

        return new PeriodoLiquidacion($year, $month);
    }

    /**
     * Calcula las fechas de referencia para el reporte.
     *
     * @param PeriodoLiquidacion $periodo Período de liquidación
     *
     * @return array Arreglo con las fechas de referencia
     */
    private function calcularFechasReferencia(PeriodoLiquidacion $periodo): array
    {
        $fechaReferencia = $periodo->getFechaReferencia();
        $fechaPrimerMes = $fechaReferencia;
        $fechaSegundoMes = $fechaReferencia->copy()->subMonth();
        $fechaTercerMes = $fechaReferencia->copy()->subMonths(2);

        Log::debug('Fechas de referencia', [
            'fechaReferencia' => $fechaReferencia,
            'fechaPrimerMes' => $fechaPrimerMes->format('Y-m'),
            'fechaSegundoMes' => $fechaSegundoMes->format('Y-m'),
            'fechaTercerMes' => $fechaTercerMes->format('Y-m'),
        ]);

        return [
            'primerMes' => $fechaPrimerMes,
            'segundoMes' => $fechaSegundoMes,
            'tercerMes' => $fechaTercerMes,
        ];
    }

    /**
     * Obtiene los legajos para cada mes de referencia.
     *
     * @param array $fechas Arreglo con las fechas de referencia
     *
     * @return array Arreglo con los legajos por mes
     */
    private function obtenerLegajosPorMes(array $fechas): array
    {
        $legajosPrimerMes = $this->legajosMes($fechas['primerMes']);
        $legajosSegundoMes = $this->legajosMes($fechas['segundoMes']);
        $legajosTercerMes = $this->legajosMes($fechas['tercerMes']);

        Log::info('Legajos obtenidos por mes:', [
            'primer_mes' => $legajosPrimerMes->count(),
            'segundo_mes' => $legajosSegundoMes->count(),
            'tercer_mes' => $legajosTercerMes->count(),
        ]);

        return [
            'primerMes' => $legajosPrimerMes,
            'segundoMes' => $legajosSegundoMes,
            'tercerMes' => $legajosTercerMes,
        ];
    }

    /**
     * Combina los legajos del primer y segundo mes eliminando duplicados.
     *
     * @param array $legajosPorMes Arreglo con los legajos por mes
     *
     * @return Collection Colección con los legajos combinados
     */
    private function combinarLegajosPrimerYSegundoMes(array $legajosPorMes): Collection
    {
        $legajosCombinados = $legajosPorMes['primerMes']
            ->concat($legajosPorMes['segundoMes'])
            ->unique('nro_legaj');

        Log::info('Legajos combinados:', [
            'total' => $legajosCombinados->count(),
        ]);

        return $legajosCombinados;
    }
}
