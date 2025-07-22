<?php

namespace App\Services;

use App\Enums\ConceptosSicossEnum;
use App\Models\ControlAportesDiferencia;
use App\Models\ControlConceptosPeriodo;
use App\Models\ControlContribucionesDiferencia;
use App\Models\ControlCuilsDiferencia;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para ejecutar controles de SICOSS.
 */
class SicossControlService
{
    use MapucheConnectionTrait;

    /** @var string Conexión de base de datos actual */
    protected string $connection;

    protected array $conceptosAportesSijp;

    protected array $conceptosAportesInssjp;

    protected array $conceptosAportes;

    protected array $conceptosContribuciones;

    protected array $conceptosContribucionesSijp;

    protected array $conceptosContribucionesInssjp;

    public function __construct()
    {
        $this->conceptosAportesSijp = ConceptosSicossEnum::getAportesSijpCodes();
        $this->conceptosAportesInssjp = ConceptosSicossEnum::getAportesInssjpCodes();
        $this->conceptosContribucionesSijp = ConceptosSicossEnum::getContribucionesSijpCodes();
        $this->conceptosContribucionesInssjp = ConceptosSicossEnum::getContribucionesInssjpCodes();
        $this->conceptosAportes = ConceptosSicossEnum::getAllAportesCodes();
        $this->conceptosContribuciones = ConceptosSicossEnum::getAllContribucionesCodes();
    }

    /**
     * Establece la conexión de base de datos a utilizar.
     *
     * @param string $connection Nombre de la conexión
     */
    public function setConnection(string $connection): self
    {
        $this->connection = $connection;
        Log::info('Conexión establecida en SicossControlService', ['connection' => $this->connection]);
        return $this;
    }

    /**
     * Ejecuta todos los controles post importación de SICOSS.
     *
     * @param int|null $year Año fiscal
     * @param int|null $month Mes fiscal
     *
     * @return array Resultados de los controles con la siguiente estructura:
     *               [
     *               'success' => bool,
     *               'resultados' => [
     *               'cuils_faltantes' => array,
     *               'registros_actualizados' => int,
     *               'diferencias_encontradas' => array
     *               ]
     *               ]
     */
    public function ejecutarControlesPostImportacion(?int $year = null, ?int $month = null): array
    {
        // Si no se proporciona año y mes, obtenerlos del servicio
        if (!$year || !$month) {
            $periodoFiscalService = app(PeriodoFiscalService::class);
            $periodoFiscal = $periodoFiscalService->getPeriodoFiscalFromDatabase();
            $year = $periodoFiscal['year'];
            $month = $periodoFiscal['month'];
        }

        // Crear tabla temporal con el período fiscal específico
        $this->crearTablaDH21Aportes($year, $month);

        return $this->controlAportesYContribuciones($year, $month);
    }

    /**
     * Ejecuta específicamente el control de aportes.
     */
    public function ejecutarControlAportes(?int $year = null, ?int $month = null): array
    {
        // Crear tabla temporal necesaria
        $this->crearTablaDH21Aportes($year, $month);

        // Ejecutar control específico de aportes
        $diferenciasAportes = $this->getDiferenciasDeAportes($year, $month);

        return [
            'diferencias_de_aportes' => $diferenciasAportes,
        ];
    }

    /**
     * Ejecuta específicamente el control de contribuciones.
     */
    public function ejecutarControlContribuciones(?int $anio = null, ?int $mes = null): array
    {
        // Crear tabla temporal necesaria
        $this->crearTablaDH21Aportes($anio, $mes);

        // Ejecutar control específico de contribuciones
        $diferenciasContribuciones = $this->obtenerDiferenciasDeContribuciones($anio, $mes);

        return [
            'diferencias_de_contribuciones' => $diferenciasContribuciones,
        ];
    }

    /**
     * Ejecuta el control por dependencia.
     *
     * @param int|null $anio Año de la liquidación
     * @param int|null $mes Mes de la liquidación
     *
     * @return array Resultados del control por dependencia
     */
    public function ejecutarControlPorDependencia(?int $anio = null, ?int $mes = null): array
    {
        // Crear tabla temporal necesaria
        $this->crearTablaDH21Aportes($anio, $mes);

        return [
            'diferencias_por_dependencia' => $this->getDiferenciasContribucionesPorDependencia(),
        ];
    }

    /**
     * Crea la tabla temporal dh21aporte con los totales de aportes y contribuciones.
     *
     * @param int|null $anio Año de la liquidación
     * @param int|null $mes Mes de la liquidación
     */
    public function crearTablaDH21Aportes(?int $anio = null, ?int $mes = null): void
    {
        // Si no se proporcionan parámetros, usar el periodo fiscal actual
        if ($anio === null || $mes === null) {
            $periodoFiscal = app(PeriodoFiscalService::class)->getPeriodoFiscalFromDatabase();
            $anio ??= $periodoFiscal['year'];
            $mes ??= $periodoFiscal['month'];
        }

        // dd([
        //     ConceptosSicossEnum::getSqlConditionAportesSijp(),
        //     ConceptosSicossEnum::getSqlConditionAportesInssjp(),
        //     ConceptosSicossEnum::getSqlConditionContribucionesSijp(),
        //     ConceptosSicossEnum::getSqlConditionContribucionesInssjp()
        // ]);


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
                    WHEN " . ConceptosSicossEnum::getSqlConditionAportesSijp() . ' THEN impp_conce * 1
                    ELSE impp_conce * 0
                END)::numeric(15,2) AS aportesijpdh21,
                SUM(CASE
                    WHEN ' . ConceptosSicossEnum::getSqlConditionAportesInssjp() . ' THEN impp_conce * 1
                    ELSE impp_conce * 0
                END)::numeric(15,2) AS aporteinssjpdh21,
                SUM(CASE
                    WHEN ' . ConceptosSicossEnum::getSqlConditionContribucionesSijp() . ' THEN impp_conce * 1
                    ELSE impp_conce * 0
                END)::numeric(15,2) AS contribucionsijpdh21,
                SUM(CASE
                    WHEN ' . ConceptosSicossEnum::getSqlConditionContribucionesInssjp() . " THEN impp_conce * 1
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
     * Obtiene las diferencias de aportes por CUIL.
     *
     * @param int|null $anio Año de la liquidación
     * @param int|null $mes Mes de la liquidación
     *
     * @return array{
     *   registros_procesados: int,
     *   diferencias_encontradas: int,
     *   fecha_proceso: string
     * }
     */
    public function getDiferenciasDeAportes(?int $anio = null, ?int $mes = null): array
    {
        // Si no se proporcionan parámetros, usar el periodo fiscal actual
        if ($anio === null || $mes === null) {
            $periodoFiscal = app(PeriodoFiscalService::class)->getPeriodoFiscalFromDatabase();
            $anio ??= $periodoFiscal['year'];
            $mes ??= $periodoFiscal['month'];
        }


        // Primero limpiamos los registros anteriores
        ControlAportesDiferencia::truncate();

        $diferencias = DB::connection($this->connection)
            ->table('dh21aporte as a')
            ->join('suc.afip_mapuche_sicoss_calculos as b', 'a.cuil', '=', 'b.cuil')
            ->whereRaw('abs(((aportesijpdh21::numeric + aporteinssjpdh21::numeric) -
                (aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re))::numeric) > 1')
            ->select([
                'a.cuil',
                DB::raw('aportesijpdh21::numeric(15,2) as aportesijpdh21'),
                DB::raw('aporteinssjpdh21::numeric(15,2) as aporteinssjpdh21'),
                DB::raw('contribucionsijpdh21::numeric(15,2) as contribucionsijpdh21'),
                DB::raw('contribucioninssjpdh21::numeric(15,2) as contribucioninssjpdh21'),
                DB::raw('aportesijp::numeric(15,2) as aportesijp'),
                DB::raw('aporteinssjp::numeric(15,2) as aporteinssjp'),
                DB::raw('contribucionsijp::numeric(15,2) as contribucionsijp'),
                DB::raw('contribucioninssjp::numeric(15,2) as contribucioninssjp'),
                DB::raw('(
                    (aportesijpdh21::numeric + aporteinssjpdh21::numeric) -
                    (aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re)
                    )::numeric(15,2) as diferencia'),
                'b.codc_uacad',
                'b.caracter',
            ])
            ->get();

        // Inserción masiva en la tabla de control
        $diferencias->map(fn ($diferencia): array => [
            'cuil' => $diferencia->cuil,
            'codc_uacad' => $diferencia->codc_uacad,
            'caracter' => $diferencia->caracter,
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
            'connection' => $this->connection,
        ])
            ->chunk(1000)->each(function ($chunk): void {
                ControlAportesDiferencia::insert($chunk->toArray());
            });

        // Retornamos solo el resumen del proceso
        return [
            'registros_procesados' => $diferencias->count(),
            'diferencias_encontradas' => $diferencias->count(),
            'fecha_proceso' => now()->toDateTimeString(),
        ];
    }

    /**
     * Obtiene las diferencias de contribuciones por CUIL.
     *
     * @param int|null $anio Año de la liquidación
     * @param int|null $mes Mes de la liquidación
     *
     * @return array Resultados del control
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

        try {
            // Consulta mejorada siguiendo el patrón que funciona correctamente
            $diferencias = DB::connection($this->connection)
                ->table('dh21aporte as a')
                ->join('suc.afip_mapuche_sicoss_calculos as b', 'a.cuil', '=', 'b.cuil')
                ->whereRaw('abs(
                ((contribucionsijpdh21 + contribucioninssjpdh21) -
                (contribucionsijp + contribucioninssjp))::numeric
            ) > 1')
                ->select([
                    'a.cuil',
                    'b.codc_uacad',
                    'b.caracter',
                    'a.nro_legaj',
                    'a.contribucionsijpdh21',
                    'a.contribucioninssjpdh21',
                    'b.contribucionsijp',
                    'b.contribucioninssjp',
                    DB::raw('((contribucionsijpdh21 + contribucioninssjpdh21) -
                    (contribucionsijp + contribucioninssjp))::numeric(15,2) as diferencia'),
                ])
                ->orderBy('diferencia', 'desc')
                ->get();


            // Inserción masiva en la tabla de control
            $diferencias->map(fn($diferencia): array => [
                'cuil' => $diferencia->cuil,
                'codc_uacad' => $diferencia->codc_uacad,
                'caracter' => $diferencia->caracter,
                'nro_legaj' => $diferencia->nro_legaj,
                'contribucionsijpdh21' => $diferencia->contribucionsijpdh21,
                'contribucioninssjpdh21' => $diferencia->contribucioninssjpdh21,
                'contribucionsijp' => $diferencia->contribucionsijp,
                'contribucioninssjp' => $diferencia->contribucioninssjp,
                'diferencia' => $diferencia->diferencia,
                'fecha_control' => now(),
                'connection' => $this->connection,
            ])->chunk(1000)->each(function ($chunk): void {
                ControlContribucionesDiferencia::insert($chunk->toArray());
            });

            return [
                'registros_procesados' => $diferencias->count(),
                'diferencias_encontradas' => $diferencias->count(),
                'fecha_proceso' => now()->toDateTimeString(),
            ];
        } catch (\Exception $e) {
            Log::error('Error al obtener diferencias de contribuciones', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'error' => true,
                'mensaje' => 'Error al procesar diferencias: ' . $e->getMessage(),
                'fecha_proceso' => now()->toDateTimeString(),
            ];
        }
    }

    /**
     * Obtiene las diferencias de aportes y contribuciones agrupadas por dependencia y carácter.
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
            ->whereRaw('abs(((contribucionsijpdh21::numeric + contribucioninssjpdh21::numeric) -
                (contribucionsijp + contribucioninssjp))::numeric) > 1')
            ->groupBy('b.codc_uacad', 'b.caracter')
            ->select([
                'b.codc_uacad',
                'b.caracter',
                DB::raw('SUM((contribucionsijpdh21::numeric + contribucioninssjpdh21::numeric) -
                    (contribucionsijp + contribucioninssjp))::numeric as diferencia_total'),
            ])
            ->orderBy('b.codc_uacad')
            ->orderBy('b.caracter')
            ->get()
            ->toArray();
    }

    /**
     * Obtiene los totales de aportes y contribuciones.
     *
     * @return array{
     *   dh21: array{aportes: float, contribuciones: float},
     *   sicoss: array{aportes: float, contribuciones: float}
     * }
     */
    public function getTotalesAportesContribuciones(): array
    {
        $totalesDH21 = DB::connection($this->connection)
            ->table('dh21aporte')
            ->select([
                DB::raw('SUM((aportesijpdh21::numeric + aporteinssjpdh21::numeric)) as aportes'),
                DB::raw('SUM((contribucionsijpdh21::numeric + contribucioninssjpdh21::numeric)) as contribuciones'),
            ])
            ->first();

        $totalesSicoss = DB::connection($this->connection)
            ->table('suc.afip_mapuche_sicoss_calculos')
            ->select([
                DB::raw('SUM((aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re)::numeric) as aportes'),
                DB::raw('SUM((contribucionsijp + contribucioninssjp)::numeric) as contribuciones'),
            ])
            ->first();

        return [
            'dh21' => [
                'aportes' => (float) $totalesDH21->aportes,
                'contribuciones' => (float) $totalesDH21->contribuciones,
            ],
            'sicoss' => [
                'aportes' => (float) $totalesSicoss->aportes,
                'contribuciones' => (float) $totalesSicoss->contribuciones,
            ],
        ];
    }

    /**
     * Obtiene el query builder para las diferencias por CUIL.
     */
    public function getQueryDiferenciasPorCuil(): Builder
    {
        return DB::connection($this->connection)
            ->table('dh21aporte as a')
            ->join('suc.afip_mapuche_sicoss_calculos as b', 'a.cuil', '=', 'b.cuil')
            ->whereRaw('abs(((aportesijpdh21::numeric + aporteinssjpdh21::numeric) -
                (aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re))::numeric) > 1')
            ->whereNotIn('a.nro_legaj', function (Builder $query): void {
                $query->select('nro_legaj')
                    ->from('mapuche.dh21')
                    ->whereIn('codn_conce', ConceptosSicossEnum::getExclusionCodes());
            })
            ->select([
                'a.nro_legaj',
                'a.cuil',
                DB::raw('((aportesijpdh21::numeric + aporteinssjpdh21::numeric) -
                    (aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re))::numeric
                    as diferencia_aportes'),
                DB::raw('((contribucionsijpdh21::numeric + contribucioninssjpdh21::numeric) -
                    (contribucionsijp + contribucioninssjp))::numeric as diferencia_contribuciones'),
            ]);
    }

    /**
     * Obtiene las diferencias de aportes y contribuciones agrupadas por dependencia y carácter.
     *
     * @return array Arreglo de resultados con diferencias de aportes y contribuciones por dependencia
     */
    public function getDiferenciasPorDependencia(): array
    {
        return DB::connection($this->connection)
            ->select('
                SELECT
                    CASE
                        WHEN d.codc_uacad IS NOT NULL THEN d.codc_uacad
                        ELSE c.codc_uacad
                    END AS codc_uacad,
                    CASE
                        WHEN d.caracter IS NOT NULL THEN d.caracter
                        ELSE c.caracter
                    END AS caracter,
                    SUM(CASE
                        WHEN d.diferencia IS NOT NULL THEN d.diferencia
                        ELSE 0
                    END)::numeric(15,2) AS diferencia_aportes,
                    SUM(CASE
                        WHEN c.diferencia IS NOT NULL THEN c.diferencia
                        ELSE 0
                    END)::numeric(15,2) AS diferencia_contribuciones
                FROM suc.control_aportes_diferencias d
                FULL JOIN suc.control_contribuciones_diferencias c ON d.cuil = c.cuil
                GROUP BY d.caracter, c.caracter, d.codc_uacad, c.codc_uacad
                ORDER BY 1
            ');
    }

    /**
     * Obtiene las diferencias de aportes agrupadas por dependencia y carácter.
     *
     * @param float $minDiferencia Diferencia mínima a considerar
     *
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
            ->whereRaw('abs(((aportesijpdh21 + aporteinssjpdh21) -
                (aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re))::numeric) > ?', [$minDiferencia])
            ->groupBy('b.codc_uacad', 'b.caracter')
            ->select([
                'b.codc_uacad',
                'b.caracter',
                DB::raw('SUM((aportesijpdh21::numeric + aporteinssjpdh21::numeric) -
                    (aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re))::numeric as diferencia_total'),
            ])
            ->orderBy('b.codc_uacad')
            ->orderBy('b.caracter')
            ->get()
            ->toArray();
    }

    /**
     * Ejecuta el control de CUILs para identificar aquellos que existen en un sistema pero no en el otro.
     *
     * @param int|null $anio Año de la liquidación
     * @param int|null $mes Mes de la liquidación
     *
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
                    DB::raw('now() as fecha_control'),
                    DB::raw("'{$this->connection}' as connection"),
                ])
                ->whereNotExists(function ($query): void {
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
                    DB::raw('now() as fecha_control'),
                    DB::raw("'{$this->connection}' as connection"),
                ])
                ->whereNotExists(function ($query): void {
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
                    'mes' => $mes,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error en ejecutarControlCuils:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'anio' => $anio,
                'mes' => $mes,
            ]);
            throw $e;
        }
    }

    /**
     * Ejecuta el control de conceptos por período fiscal.
     *
     * @param int|null $anio Año de la liquidación
     * @param int|null $mes Mes de la liquidación
     *
     * @return array{
     *   registros_procesados: int,
     *   conceptos_aportes: int,
     *   conceptos_contribuciones: int,
     *   total_aportes: float,
     *   total_contribuciones: float,
     *   fecha_proceso: string
     * }
     */
    public function ejecutarControlConceptos(?int $anio = null, ?int $mes = null): array
    {
        try {
            // Si no se proporcionan parámetros, usar el periodo fiscal actual
            if ($anio === null || $mes === null) {
                $periodoFiscal = app(PeriodoFiscalService::class)->getPeriodoFiscalFromDatabase();
                $anio ??= $periodoFiscal['year'];
                $mes ??= $periodoFiscal['month'];
            }

            // Lista de conceptos a controlar
            $conceptosAportes = ConceptosSicossEnum::getAllAportesCodes();
            $conceptosContribuciones = ConceptosSicossEnum::getAllContribucionesCodes();
            $conceptosArt = ConceptosSicossEnum::getContribucionesArtCodes();
            $conceptos = array_merge($conceptosAportes, $conceptosContribuciones, $conceptosArt);

            Log::info('Ejecutando control de conceptos', [
                'connection' => $this->connection,
                'year' => $anio,
                'month' => $mes,
                'conceptos_count' => \count($conceptos),
            ]);

            // Ejecutar la consulta
            $resultados = DB::connection($this->connection)
                ->table(DB::raw('(SELECT * FROM mapuche.dh21 UNION ALL SELECT * FROM mapuche.dh21h) as h21'))
                ->join('mapuche.dh12 as h12', function ($join): void {
                    $join->on('h21.codn_conce', '=', 'h12.codn_conce');
                })
                ->whereIn('h21.codn_conce', $conceptos)
                ->whereIn('h21.nro_liqui', function ($query) use ($anio, $mes): void {
                    $query->select('d22.nro_liqui')
                        ->from('mapuche.dh22 as d22')
                        ->where('d22.sino_genimp', true)
                        ->where('d22.per_liano', $anio)
                        ->where('d22.per_limes', $mes);
                })
                ->groupBy('h21.codn_conce', 'h12.desc_conce')
                ->orderBy('h21.codn_conce')
                ->select('h21.codn_conce', 'h12.desc_conce', DB::raw('SUM(impp_conce)::numeric(15, 2) as importe'))
                ->get();

            // Eliminar registros anteriores para este período
            ControlConceptosPeriodo::where('year', $anio)
                ->where('month', $mes)
                ->where('connection_name', $this->connection)
                ->delete();

            // Guardar los nuevos resultados
            $registrosInsertados = 0;
            foreach ($resultados as $resultado) {
                ControlConceptosPeriodo::create([
                    'year' => $anio,
                    'month' => $mes,
                    'codn_conce' => $resultado->codn_conce,
                    'desc_conce' => $resultado->desc_conce,
                    'importe' => $resultado->importe,
                    'connection_name' => $this->connection,
                ]);
                $registrosInsertados++;
            }

            // Calcular totales para el resumen
            $totalAportes = $resultados->whereIn('codn_conce', $conceptosAportes)->sum('importe');
            $totalContribuciones = $resultados->whereIn('codn_conce', $conceptosContribuciones)->sum('importe');

            return [
                'registros_procesados' => $registrosInsertados,
                'conceptos_aportes' => $resultados->whereIn('codn_conce', $conceptosAportes)->count(),
                'conceptos_contribuciones' => $resultados->whereIn('codn_conce', $conceptosContribuciones)->count(),
                'total_aportes' => (float) $totalAportes,
                'total_contribuciones' => (float) $totalContribuciones,
                'fecha_proceso' => now()->toDateTimeString(),
                'periodo' => [
                    'anio' => $anio,
                    'mes' => $mes,
                ],
                'resultados' => $resultados, // Para notificaciones detalladas
            ];
        } catch (\Exception $e) {
            Log::error('Error en ejecutarControlConceptos:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'anio' => $anio,
                'mes' => $mes,
            ]);
            throw $e;
        }
    }

    protected function getConnection(): string
    {
        if ($this->connection === '' || $this->connection === '0') {
            throw new \RuntimeException('Debe establecer una conexión antes de ejecutar los controles');
        }
        return $this->connection;
    }

    /**
     * Control de Aportes y Contribuciones
     * Compara los aportes y contribuciones calculados en DH21 vs los registrados en SICOSS.
     *
     * @return array{
     *  diferencias_por_cuil: array,
     *  diferencias_por_dependencia: array,
     *  totales: array
     * }
     */
    protected function controlAportesYContribuciones(int $year, int $month): array
    {
        // Crear tabla temporal con totales de DH21
        $this->crearTablaDH21Aportes($year, $month);

        return [
            'diferencias_de_aportes' => $this->getDiferenciasDeAportes($year, $month),
            'diferencias_por_dependencia' => $this->getDiferenciasPorDependencia(),
            'diferencias_de_contribuciones' => $this->obtenerDiferenciasDeContribuciones($year, $month),
            'totales' => $this->getTotalesAportesContribuciones(),
        ];
    }
}
