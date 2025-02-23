<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ControlArtDiferencia;
use App\Models\ControlCuilsDiferencia;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Query\Builder;
use App\Models\ControlAportesDiferencia;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Models\ControlContribucionesDiferencia;

/**
 * Servicio para ejecutar controles de SICOSS
 */
class SicossControlService
{
    use MapucheConnectionTrait;

    /** @var string Conexión de base de datos actual */
    protected string $connection;

    /**
     * Establece la conexión de base de datos a utilizar
     *
     * @param string $connection Nombre de la conexión
     * @return self
     */
    public function setConnection(string $connection): self
    {
        $this->connection = $connection;
        return $this;
    }

    protected function getConnection(): string
    {
        if (!$this->connection) {
            throw new \RuntimeException('Debe establecer una conexión antes de ejecutar los controles');
        }
        return $this->connection;
    }

    /**
     * Ejecuta todos los controles post importación de SICOSS
     *
     * @return array Resultados de los controles con la siguiente estructura:
     * [
     *     'success' => bool,
     *     'resultados' => [
     *         'cuils_faltantes' => array,
     *         'registros_actualizados' => int,
     *         'diferencias_encontradas' => array
     *     ]
     * ]
     */
    public function ejecutarControlesPostImportacion(): array
    {
        return $this->controlAportesContribuciones();
    }

    /**
     * Ejecuta específicamente el control de aportes
     */
    public function ejecutarControlAportes(?int $anio = null, ?int $mes = null): array
    {
        // Crear tabla temporal necesaria
        $this->crearTablaDH21Aportes($anio, $mes);

        // Ejecutar control específico de aportes
        $diferenciasAportes = $this->obtenerDiferenciasDeAportes($anio, $mes);

        return [
            'diferencias_de_aportes' => $diferenciasAportes,
        ];
    }

    /**
     * Ejecuta específicamente el control de contribuciones
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
     * Ejecuta el control de diferencias por dependencia
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
     * Control de Aportes y Contribuciones
     * Compara los aportes y contribuciones calculados en DH21 vs los registrados en SICOSS
     *
     * @return array{
     *  diferencias_por_cuil: array,
     *  diferencias_por_dependencia: array,
     *  totales: array
     * }
     */
    protected function controlAportesContribuciones(): array
    {
        // Crear tabla temporal con totales de DH21
        $this->crearTablaDH21Aportes();

        return [
            'diferencias_de_aportes' => $this->obtenerDiferenciasDeAportes(),
            'diferencias_por_dependencia' => [
                'diferencias_aportes_dependencia' => $this->getDiferenciasAportesPorDependencia(),
                'diferencias_contribuciones_dependencia' => $this->getDiferenciasContribucionesPorDependencia(),
            ],
            'totales' => $this->obtenerTotalesAportesContribuciones()
        ];
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
            $anio = $anio ?? $periodoFiscal['year'];
            $mes = $mes ?? $periodoFiscal['month'];
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
                'diferencia' => $diferencia->diferencia,
                'fecha_control' => now()
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
     * Obtiene el query builder para las diferencias por CUIL
     */
    public function getQueryDiferenciasPorCuil(): Builder
    {
        return DB::connection($this->connection)
            ->table('dh21aporte as a')
            ->join('suc.afip_mapuche_sicoss_calculos as b', 'a.cuil', '=', 'b.cuil')
            ->whereRaw("abs(((aportesijpdh21::numeric + aporteinssjpdh21::numeric) -
                (aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re))::numeric) > 1")
            ->whereNotIn('a.nro_legaj', function (Builder $query) {
                $query->select('nro_legaj')
                    ->from('mapuche.dh21')
                    ->whereIn('codn_conce', [123, 248]);
            })
            ->select([
                'a.nro_legaj',
                'a.cuil',
                DB::raw("((aportesijpdh21::numeric + aporteinssjpdh21::numeric) -
                    (aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re))::numeric
                    as diferencia_aportes"),
                DB::raw("((contribucionsijpdh21::numeric + contribucioninssjpdh21::numeric) -
                    (contribucionsijp + contribucioninssjp))::numeric as diferencia_contribuciones")
            ]);
    }

    /**
     * Obtiene el query builder para las diferencias por dependencia
     */
    // public function getQueryContribucionesDiferenciasPorDependencia(): Builder
    // {
    //     return DB::connection($this->connection)
    //         ->table('dh21aporte as a')
    //         ->join('suc.afip_mapuche_sicoss_calculos as b', 'a.cuil', '=', 'b.cuil')
    //         ->whereRaw("abs(((contribucionsijpdh21::numeric + contribucioninssjpdh21::numeric) -
    //             (contribucionsijp + contribucioninssjp))::numeric) > 1")
    //         ->groupBy('b.codc_uacad', 'b.caracter')
    //         ->select([
    //             'b.codc_uacad',
    //             'b.caracter',
    //             DB::raw("SUM((contribucionsijpdh21::numeric + contribucioninssjpdh21::numeric) -
    //                 (contribucionsijp + contribucioninssjp))::numeric as diferencia_total")
    //         ]);
    // }

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
}
