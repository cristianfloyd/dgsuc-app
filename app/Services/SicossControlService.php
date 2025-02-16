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
        return [
            'aportes_contribuciones' => $this->controlAportesContribuciones(),
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
            'diferencias_por_cuil' => $this->obtenerDiferenciasPorCuil(),
            'diferencias_por_dependencia' => $this->obtenerDiferenciasPorDependencia(),
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
     * Obtiene las diferencias de aportes y contribuciones por CUIL
     *
     * @return array<int, array{
     *   nro_legaj: int,
     *   cuil: string,
     *   diferencia_aportes: float,
     *   diferencia_contribuciones: float
     * }>
     */
    public function obtenerDiferenciasPorCuil(): array
    {
        // Primero limpiamos los registros anteriores
        ControlAportesDiferencia::truncate();

        $diferencias = DB::connection($this->connection)
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
                'a.cuil',
                DB::raw("aportesijpdh21::numeric(15,2) as aportesijpdh21"),
                DB::raw("aporteinssjpdh21::numeric(15,2) as aporteinssjpdh21"),
                DB::raw("((aportesijpdh21::numeric + aporteinssjpdh21::numeric) -
                    (aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re))::numeric(15,2)
                    as diferencia")
            ])
            ->get();

        // Almacenamos las diferencias encontradas
        foreach ($diferencias as $diferencia) {
            ControlAportesDiferencia::create([
                'cuil' => $diferencia->cuil,
                'aportesijpdh21' => $diferencia->aportesijpdh21,
                'aporteinssjpdh21' => $diferencia->aporteinssjpdh21,
                'diferencia' => $diferencia->diferencia,
                'fecha_control' => now()
            ]);
        }

        return $diferencias->toArray();
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
    public function obtenerDiferenciasPorDependencia(): array
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
    public function getQueryDiferenciasPorDependencia(): Builder
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
            ]);
    }
}
