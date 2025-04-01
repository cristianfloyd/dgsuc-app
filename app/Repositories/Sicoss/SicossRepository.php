<?php

namespace App\Repositories\Sicoss;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ControlCuilsDiferencia;
use Illuminate\Database\Query\Builder;
use App\Models\ControlConceptosPeriodo;
use App\Models\ControlAportesDiferencia;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Models\ControlContribucionesDiferencia;

/**
 * Repositorio para acceso a datos relacionados con SICOSS
 * Centraliza todas las consultas a la base de datos
 */
class SicossRepository
{
    /**
     * Conexión de base de datos actual
     */
    protected string $connection;

    /**
     * Establece la conexión a utilizar
     */
    public function setConnection(string $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * Obtiene la conexión actual
     */
    public function getConnection(): string
    {
        return $this->connection;
    }

    /**
     * Obtiene estadísticas de resumen para el panel de control
     */
    public function getResumenStats(int $year, int $month): array
    {
        try {
            // Obtener totales de diferencias
            $totalesDiferencias = $this->getTotalesDiferencias();
            
            // Obtener diferencias por dependencia
            $diferenciasPorDependencia = $this->getDiferenciasPorDependencia();
            
            // Obtener totales monetarios
            $totalesMonetarios = $this->getTotalesMonetarios();
            
            // Obtener comparación con 931
            $comparacion931 = $this->getComparacion931();
            
            // Obtener CUILs no encontrados
            $cuilsNoEncontrados = $this->getCuilsNoEncontrados();
            
            return [
                'totales' => $totalesDiferencias,
                'diferencias_por_dependencia' => $diferenciasPorDependencia,
                'totales_monetarios' => $totalesMonetarios,
                'comparacion_931' => $comparacion931,
                'cuils_no_encontrados' => $cuilsNoEncontrados,
            ];
        } catch (\Exception $e) {
            logger()->error('Error obteniendo resumen stats:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [];
        }
    }

    /**
     * Obtiene los totales de diferencias
     */
    private function getTotalesDiferencias(): array
    {
        return [
            'cuils_procesados' => ControlAportesDiferencia::count(),
            'cuils_con_diferencias_aportes' => ControlAportesDiferencia::where(DB::raw('ABS(diferencia)'), '>', 1)->count(),
            'cuils_con_diferencias_contribuciones' => ControlContribucionesDiferencia::where(DB::raw('ABS(diferencia)'), '>', 1)->count(),
        ];
    }

    /**
     * Obtiene las diferencias por dependencia
     */
    public function getDiferenciasPorDependencia(): array
    {
        return DB::connection($this->connection)
            ->table('dh21aporte as a')
            ->join('suc.afip_mapuche_sicoss_calculos as b', 'a.cuil', '=', 'b.cuil')
            ->select('b.codc_uacad', 'b.caracter')
            ->selectRaw('SUM((a.contribucionsijpdh21::numeric + a.contribucioninssjpdh21::numeric) -
                (b.contribucionsijp + b.contribucioninssjp))::numeric as diferencia_total')
            ->whereRaw('ABS(((a.contribucionsijpdh21::numeric + a.contribucioninssjpdh21::numeric) -
                (b.contribucionsijp + b.contribucioninssjp))::numeric) > 1')
            ->groupBy('b.codc_uacad', 'b.caracter')
            ->orderBy('b.codc_uacad')
            ->orderBy('b.caracter')
            ->get()
            ->toArray();
    }

    /**
     * Obtiene los totales monetarios
     */
    private function getTotalesMonetarios(): array
    {
        return [
            'diferencia_aportes' => ControlAportesDiferencia::sum('diferencia'),
            'diferencia_contribuciones' => ControlContribucionesDiferencia::sum('diferencia'),
        ];
    }

    /**
     * Obtiene la comparación con 931
     */
    private function getComparacion931(): array
    {
        return [
            'aportes' => [
                'dh21' => DB::connection($this->connection)->table('dh21aporte')->sum(DB::raw('aportesijpdh21 + aporteinssjpdh21')),
                'sicoss' => DB::connection($this->connection)->table('suc.afip_mapuche_sicoss_calculos')
                    ->sum(DB::raw('aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re')),
            ],
            'contribuciones' => [
                'dh21' => DB::connection($this->connection)->table('dh21aporte')->sum(DB::raw('contribucionsijpdh21 + contribucioninssjpdh21')),
                'sicoss' => DB::connection($this->connection)->table('suc.afip_mapuche_sicoss_calculos')->sum(DB::raw('contribucionsijp + contribucioninssjp')),
            ],
        ];
    }

    /**
     * Obtiene los CUILs no encontrados
     */
    private function getCuilsNoEncontrados(): array
    {
        return [
            'en_dh21' => DB::connection($this->connection)
                ->table('dh21aporte')
                ->whereNotIn('cuil', function ($query) {
                    $query->select('cuil')->from('suc.afip_mapuche_sicoss_calculos');
                })
                ->count(),
            'en_sicoss' => DB::connection($this->connection)
                ->table('suc.afip_mapuche_sicoss_calculos')
                ->whereNotIn('cuil', function ($query) {
                    $query->select('cuil')->from('dh21aporte');
                })
                ->count(),
        ];
    }

    /**
     * Obtiene los conceptos por período
     */
    public function getConceptosPorPeriodo(int $year, int $month, array $conceptos): array
    {
        return DB::connection($this->connection)
            ->table('mapuche.dh21h as h21')
            ->join('mapuche.dh12 as h12', function ($join) {
                $join->on('h21.codn_conce', '=', 'h12.codn_conce');
            })
            ->whereIn('h21.codn_conce', $conceptos)
            ->whereIn('h21.nro_liqui', function ($query) use ($year, $month) {
                $query->select('d22.nro_liqui')
                    ->from('mapuche.dh22 as d22')
                    ->where('d22.sino_genimp', true)
                    ->where('d22.per_liano', $year)
                    ->where('d22.per_limes', $month);
            })
            ->groupBy('h21.codn_conce', 'h12.desc_conce')
            ->orderBy('h21.codn_conce')
            ->select('h21.codn_conce', 'h12.desc_conce', DB::raw('SUM(impp_conce)::numeric(15, 2) as importe'))
            ->get()
            ->toArray();
    }

    /**
     * Crea la tabla temporal dh21aporte con los totales de aportes y contribuciones
     *
     * @param int|null $anio Año de la liquidación
     * @param int|null $mes Mes de la liquidación
     * @return void
     */
    public function crearTablaDH21Aportes(?int $anio = null, ?int $mes = null): void
    {
        // Si no se proporcionan parámetros, usar el periodo fiscal actual
        if ($anio === null || $mes === null) {
            $periodoFiscal = app(PeriodoFiscalService::class)->getPeriodoFiscalFromDatabase();
            $anio ??= $periodoFiscal['year'];
            $mes ??= $periodoFiscal['month'];
        }

        // Determinar qué tabla usar basado en el período
        $periodoActual = app(PeriodoFiscalService::class)->getPeriodoFiscalFromDatabase();
        $tablaNovedad = ($anio == $periodoActual['year'] && $mes == $periodoActual['month'])
            ? 'mapuche.dh21'
            : 'mapuche.dh21h';
        Log::info('Tabla Novedad', ['tabla' => $tablaNovedad]);
        DB::connection($this->connection)->unprepared("
            DROP TABLE IF EXISTS dh21aporte;

            SELECT
                {$tablaNovedad}.nro_legaj,
                (nro_cuil1::CHAR(2) || LPAD(nro_cuil::CHAR(8), 8, '0') || nro_cuil2::CHAR(1)) AS cuil,
                SUM(CASE
                    WHEN codn_conce IN (201, 202, 203, 205, 204) THEN impp_conce * 1
                    ELSE impp_conce * 0
                END)::numeric(15,2) AS aportesijpdh21,
                SUM(CASE
                    WHEN codn_conce IN (247) THEN impp_conce * 1
                    ELSE impp_conce * 0
                END)::numeric(15,2) AS aporteinssjpdh21,
                SUM(CASE
                    WHEN codn_conce IN (301, 302, 303, 304, 307) THEN impp_conce * 1
                    ELSE impp_conce * 0
                END)::numeric(15,2) AS contribucionsijpdh21,
                SUM(CASE
                    WHEN codn_conce IN (347) THEN impp_conce * 1
                    ELSE impp_conce * 0
                END)::numeric(15,2) AS contribucioninssjpdh21
            INTO TEMP dh21aporte
            FROM {$tablaNovedad}
            JOIN mapuche.dh01 ON {$tablaNovedad}.nro_legaj = mapuche.dh01.nro_legaj
            WHERE nro_liqui IN(
                SELECT d22.nro_liqui FROM mapuche.dh22 d22
                WHERE d22.sino_genimp = true
                AND d22.per_liano = {$anio}
                AND per_limes = {$mes}
                ORDER BY 1
            )
            GROUP BY {$tablaNovedad}.nro_legaj, nro_cuil1, nro_cuil, nro_cuil2;
        ");
    }

    /**
     * Obtiene los conteos de registros
     */
    public function getConteos(): array
    {
        return [
            'dh21aporte' => DB::connection($this->connection)
                ->table('dh21aporte')
                ->count(),

            'sicoss_calculos' => DB::connection($this->connection)
                ->table('suc.afip_mapuche_sicoss_calculos')
                ->count(),

            'sicoss' => DB::connection($this->connection)
                ->table('suc.afip_mapuche_sicoss')
                ->count(),
        ];
    }

    /**
     * Obtiene las diferencias de aportes por CUIL
     *
     * @param int|null $anio Año de la liquidación
     * @param int|null $mes Mes de la liquidación
     * @return array{
     *   registros_procesados: int,
     *   diferencias_encontradas: int,
     *   fecha_proceso: string
     * }
     */
    public function obtenerDiferenciasDeAportes(?int $anio = null, ?int $mes = null): array
    {
        // Si no se proporcionan parámetros, usar el periodo fiscal actual
        if ($anio === null || $mes === null) {
            $periodoFiscal = app(PeriodoFiscalService::class)->getPeriodoFiscalFromDatabase();
            $anio ??= $periodoFiscal['year'];
            $mes ??= $periodoFiscal['month'];
        }

        // Determinar qué tabla usar basado en el período
        $periodoActual = app(PeriodoFiscalService::class)->getPeriodoFiscalFromDatabase();
        $tablaNovedad = ($anio == $periodoActual['year'] && $mes == $periodoActual['month'])
            ? 'mapuche.dh21'
            : 'mapuche.dh21h';

        // Primero limpiamos los registros anteriores
        ControlAportesDiferencia::truncate();

        $diferencias = DB::connection($this->connection)
            ->table('dh21aporte as a')
            ->join('suc.afip_mapuche_sicoss_calculos as b', 'a.cuil', '=', 'b.cuil')
            ->whereRaw("abs(((aportesijpdh21::numeric + aporteinssjpdh21::numeric) -
                (aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re))::numeric) > 1")
            ->whereNotIn('a.nro_legaj', function (Builder $query) use ($tablaNovedad) {
                $query->select('nro_legaj')
                    ->from($tablaNovedad)
                    ->whereIn('codn_conce', [123, 248]);
            })
            ->select([
                'a.cuil',
                DB::raw("aportesijpdh21::numeric(15,2) as aportesijpdh21"),
                DB::raw("aporteinssjpdh21::numeric(15,2) as aporteinssjpdh21"),
                DB::raw("contribucionsijpdh21::numeric(15,2) as contribucionsijpdh21"),
                DB::raw("contribucioninssjpdh21::numeric(15,2) as contribucioninssjpdh21"),
                DB::raw("aportesijp::numeric(15,2) as aportesijp"),
                DB::raw("aporteinssjp::numeric(15,2) as aporteinssjp"),
                DB::raw("contribucionsijp::numeric(15,2) as contribucionsijp"),
                DB::raw("contribucioninssjp::numeric(15,2) as contribucioninssjp"),
                DB::raw("((aportesijpdh21::numeric + aporteinssjpdh21::numeric) -
                    (aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re))::numeric(15,2)
                    as diferencia")
            ])
            ->get();

        // Inserción masiva en la tabla de control
        $registrosParaInsertar = $diferencias->map(function ($diferencia) {
            return [
                'cuil' => $diferencia->cuil,
                'aportesijpdh21' => $diferencia->aportesijpdh21,
                'aporteinssjpdh21' => $diferencia->aporteinssjpdh21,
                'contribucionsijpdh21' => $diferencia->contribucionsijpdh21,
                'contribucioninssjpdh21' => $diferencia->contribucioninssjpdh21,
                'aportesijp' => $diferencia->aportesijp,
                'aporteinssjp' => $diferencia->aporteinssjp,
                'contribucionsijp' => $diferencia->contribucionsijp,
                'contribucioninssjp' => $diferencia->contribucioninssjp,
                'diferencia' => $diferencia->diferencia,
                'fecha_control' => now(),
                'connection' => $this->connection
            ];
        })->chunk(1000)->each(function ($chunk) {
            ControlAportesDiferencia::insert($chunk->toArray());
        });

        // Retornamos solo el resumen del proceso
        return [
            'registros_procesados' => $diferencias->count(),
            'diferencias_encontradas' => $diferencias->count(),
            'fecha_proceso' => now()->toDateTimeString()
        ];
    }

    /**
     * Obtiene las diferencias de aportes y contribuciones agrupadas por dependencia y carácter
     *
     * @return array<int, array{
     *   codc_uacad: string,
     *   caracter: string,
     *   diferencia_total: float
     * }>
     */
    public function getDiferenciasContribucionesPorDependencia(): array
    {
        return DB::connection($this->connection)
            ->table('dh21aporte as a')
            ->join('suc.afip_mapuche_sicoss_calculos as b', 'a.cuil', '=', 'b.cuil')
            ->whereRaw("abs(((contribucionsijpdh21::numeric + contribucioninssjpdh21::numeric) -
                (contribucionsijp + contribucioninssjp))::numeric) > 1")
            ->groupBy('b.codc_uacad', 'b.caracter')
            ->select([
                'b.codc_uacad',
                'b.caracter',
                DB::raw("SUM((contribucionsijpdh21::numeric + contribucioninssjpdh21::numeric) -
                    (contribucionsijp + contribucioninssjp))::numeric as diferencia_total")
            ])
            ->orderBy('b.codc_uacad')
            ->orderBy('b.caracter')
            ->get()
            ->toArray();
    }

    /**
     * Obtiene las diferencias de aportes agrupadas por dependencia y carácter
     *
     * @param float $minDiferencia Diferencia mínima a considerar
     * @return array<int, array{
     *   codc_uacad: string,
     *   caracter: string,
     *   diferencia_total: float
     * }>
     */
    public function getDiferenciasAportesPorDependencia(float $minDiferencia = 1): array
    {
        return DB::connection($this->connection)
            ->table('dh21aporte as a')
            ->join('suc.afip_mapuche_sicoss_calculos as b', 'a.cuil', '=', 'b.cuil')
            ->whereRaw("abs(((aportesijpdh21::numeric + aporteinssjpdh21::numeric) -
                (aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re))::numeric) > ?", [$minDiferencia])
            ->groupBy('b.codc_uacad', 'b.caracter')
            ->select([
                'b.codc_uacad',
                'b.caracter',
                DB::raw("SUM((aportesijpdh21::numeric + aporteinssjpdh21::numeric) -
                    (aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re))::numeric as diferencia_total")
            ])
            ->orderBy('b.codc_uacad')
            ->orderBy('b.caracter')
            ->get()
            ->toArray();
    }

    /**
     * Obtiene los totales de aportes y contribuciones
     *
     * @return array{
     *   dh21: array{aportes: float, contribuciones: float},
     *   sicoss: array{aportes: float, contribuciones: float}
     * }
     */
    public function obtenerTotalesAportesContribuciones(): array
    {
        $totalesDH21 = DB::connection($this->connection)
            ->table('dh21aporte')
            ->select([
                DB::raw('SUM((aportesijpdh21::numeric + aporteinssjpdh21::numeric)) as aportes'),
                DB::raw('SUM((contribucionsijpdh21::numeric + contribucioninssjpdh21::numeric)) as contribuciones')
            ])
            ->first();

        $totalesSicoss = DB::connection($this->connection)
            ->table('suc.afip_mapuche_sicoss_calculos')
            ->select([
                DB::raw('SUM((aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re)::numeric) as aportes'),
                DB::raw('SUM((contribucionsijp + contribucioninssjp)::numeric) as contribuciones')
            ])
            ->first();

        return [
            'dh21' => [
                'aportes' => (float) $totalesDH21->aportes,
                'contribuciones' => (float) $totalesDH21->contribuciones
            ],
            'sicoss' => [
                'aportes' => (float) $totalesSicoss->aportes,
                'contribuciones' => (float) $totalesSicoss->contribuciones
            ]
        ];
    }

    /**
     * Obtiene las diferencias de contribuciones por CUIL
     *
     * @param int|null $anio Año de la liquidación
     * @param int|null $mes Mes de la liquidación
     * @return array{
     *   registros_procesados: int,
     *   diferencias_encontradas: int,
     *   fecha_proceso: string
     * }
     */
    public function obtenerDiferenciasDeContribuciones(?int $anio = null, ?int $mes = null): array
    {
        // Si no se proporcionan parámetros, usar el periodo fiscal actual
        if ($anio === null || $mes === null) {
            $periodoFiscal = app(PeriodoFiscalService::class)->getPeriodoFiscalFromDatabase();
            $anio ??= $periodoFiscal['year'];
            $mes ??= $periodoFiscal['month'];
        }

        // Primero limpiamos los registros anteriores
        ControlContribucionesDiferencia::truncate();

        $diferencias = DB::connection($this->connection)
            ->table('dh21aporte as a')
            ->join('suc.afip_mapuche_sicoss_calculos as b', 'a.cuil', '=', 'b.cuil')
            ->whereRaw("abs(((contribucionsijpdh21::numeric + contribucioninssjpdh21::numeric) -
                (contribucionsijp + contribucioninssjp))::numeric) > 1")
            ->select([
                'a.cuil',
                'a.nro_legaj',
                DB::raw("contribucionsijpdh21::numeric(15,2) as contribucionsijpdh21"),
                DB::raw("contribucioninssjpdh21::numeric(15,2) as contribucioninssjpdh21"),
                DB::raw("contribucionsijp::numeric(15,2) as contribucionsijp"),
                DB::raw("contribucioninssjp::numeric(15,2) as contribucioninssjp"),
                DB::raw("((contribucionsijpdh21::numeric + contribucioninssjpdh21::numeric) -
                    (contribucionsijp + contribucioninssjp))::numeric(15,2) as diferencia")
            ])
            ->get();

        $registrosParaInsertar = $diferencias->map(function ($diferencia) {
            return [
                'cuil' => $diferencia->cuil,
                'nro_legaj' => $diferencia->nro_legaj,
                'contribucionsijpdh21' => $diferencia->contribucionsijpdh21,
                'contribucioninssjpdh21' => $diferencia->contribucioninssjpdh21,
                'contribucionsijp' => $diferencia->contribucionsijp,
                'contribucioninssjp' => $diferencia->contribucioninssjp,
                'diferencia' => $diferencia->diferencia,
                'fecha_control' => now()
            ];
        })->chunk(1000)->each(function ($chunk) {
            ControlContribucionesDiferencia::insert($chunk->toArray());
        });

        return [
            'registros_procesados' => $diferencias->count(),
            'diferencias_encontradas' => $diferencias->count(),
            'fecha_proceso' => now()->toDateTimeString()
        ];
    }

    /**
     * Ejecuta el control de CUILs para identificar aquellos que existen en un sistema pero no en el otro
     *
     * @param int|null $anio Año de la liquidación
     * @param int|null $mes Mes de la liquidación
     * @return array{
     *   registros_procesados: int,
     *   cuils_solo_dh21: int,
     *   cuils_solo_sicoss: int,
     *   fecha_proceso: string
     * }
     */
    public function ejecutarControlCuils(?int $anio = null, ?int $mes = null): array
    {
        try {
            // Si no se proporcionan parámetros, usar el periodo fiscal actual
            if ($anio === null || $mes === null) {
                $periodoFiscal = app(PeriodoFiscalService::class)->getPeriodoFiscalFromDatabase();
                $anio ??= $periodoFiscal['year'];
                $mes ??= $periodoFiscal['month'];
            }

            // Crear tabla temporal necesaria
            $this->crearTablaDH21Aportes($anio, $mes);

            // Primero limpiamos los registros anteriores
            ControlCuilsDiferencia::truncate();

            // Obtener CUILs que están en DH21 pero no en SICOSS
            $cuilsSoloDh21 = DB::connection($this->connection)
                ->table('dh21aporte as a')
                ->select([
                    'a.cuil',
                    DB::raw("'DH21' as origen"),
                    DB::raw("now() as fecha_control"),
                    DB::raw("'{$this->connection}' as connection")
                ])
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('suc.afip_mapuche_sicoss_calculos as b')
                        ->whereColumn('a.cuil', 'b.cuil');
                });

            // Obtener CUILs que están en SICOSS pero no en DH21
            $cuilsSoloSicoss = DB::connection($this->connection)
                ->table('suc.afip_mapuche_sicoss_calculos as b')
                ->select([
                    'b.cuil',
                    DB::raw("'SICOSS' as origen"),
                    DB::raw("now() as fecha_control"),
                    DB::raw("'{$this->connection}' as connection")
                ])
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('dh21aporte as a')
                        ->whereColumn('b.cuil', 'a.cuil');
                });

            // Unir los resultados y hacer la inserción
            $insertQuery = $cuilsSoloDh21->union($cuilsSoloSicoss);

            // Insertar directamente usando una subconsulta
            DB::connection($this->connection)->statement("
                INSERT INTO suc.control_cuils_diferencias (cuil, origen, fecha_control, connection)
                {$insertQuery->toSql()}
            ", $insertQuery->getBindings());

            // Obtener conteos para el resumen
            $conteos = [
                'cuils_solo_dh21' => ControlCuilsDiferencia::where('origen', 'DH21')->count(),
                'cuils_solo_sicoss' => ControlCuilsDiferencia::where('origen', 'SICOSS')->count(),
            ];

            return [
                'registros_procesados' => $conteos['cuils_solo_dh21'] + $conteos['cuils_solo_sicoss'],
                'cuils_solo_dh21' => $conteos['cuils_solo_dh21'],
                'cuils_solo_sicoss' => $conteos['cuils_solo_sicoss'],
                'fecha_proceso' => now()->toDateTimeString(),
                'periodo' => [
                    'anio' => $anio,
                    'mes' => $mes
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error en ejecutarControlCuils:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'anio' => $anio,
                'mes' => $mes
            ]);
            throw $e;
        }
    }

    public function getTotalAporteDh21(): float
    {
        $totalAporteDh21 = DB::connection($this->connection)
            ->table('dh21aporte')
            ->select(DB::raw('SUM(aportesijpdh21::numeric + aporteinssjpdh21::numeric) as total_aportes'))
            ->first();

        return (float) $totalAporteDh21->total_aportes;
    }

    public function getTotalContribucionesDh21(): float
    {
        $totalContribucionesDh21 = DB::connection($this->connection)
            ->table('dh21aporte')
            ->select(DB::raw('SUM(contribucionsijpdh21::numeric + contribucioninssjpdh21::numeric) as total_contribuciones'))
            ->first();

        return (float) $totalContribucionesDh21->total_contribuciones;
    }

    public function getTotalAportesSicoss(): float
    {
        $totalAportesSicoss = DB::connection($this->connection)
            ->table('suc.afip_mapuche_sicoss_calculos')
            ->select(DB::raw('SUM(aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re::numeric) as total_aportes'))
            ->first();

        return (float) $totalAportesSicoss->total_aportes;
    }

    public function getTotalContribucionesSicoss(): float
    {
        $totalContribucionesSicoss = DB::connection($this->connection)
            ->table('suc.afip_mapuche_sicoss_calculos')
            ->select(DB::raw('SUM(contribucionsijp + contribucioninssjp::numeric) as total_contribuciones'))
            ->first();

        return (float) $totalContribucionesSicoss->total_contribuciones;
    }
}
