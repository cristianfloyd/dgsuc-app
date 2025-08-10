<?php

namespace App\Repositories;

use App\Contracts\Dh21RepositoryInterface;
use App\Models\Dh21;
use App\NroLiqui;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class Dh21Repository implements Dh21RepositoryInterface
{
    use MapucheConnectionTrait;

    /**
     * Devuelve la cantidad de legajos distintos en el modelo Dh21.
     *
     * @return int La cantidad de legajos distintos.
     */
    public function getDistinctLegajos(): int
    {
        return Dh21::query()->distinct('nro_legaj')->count();
    }

    /**
     * Devuelve una instancia de Illuminate\Database\Eloquent\Builder que se puede usar para consultar el modelo Dh21.
     *
     * @return Builder
     */
    public function query(): Builder
    {
        return Dh21::query();
    }

    /**
     * Devuelve la suma total del concepto 101 para un número de liquidación dado.
     *
     * @param NroLiqui|null $nroLiqui El número de liquidación para filtrar los registros. Si se omite, se devuelve la suma total de todos los registros.
     *
     * @return float La suma total del concepto 101.
     */
    public function getTotalConcepto101(?NroLiqui $nroLiqui = null): float
    {
        $query = Dh21::query()->where('codn_conce', '101');
        if ($nroLiqui) {
            $query->where('nro_liqui', $nroLiqui->getValue());
        }
        return $query->sum('impp_conce');
    }

    /**
     * Obtiene las horas y días trabajados para un legajo y cargo específico.
     *
     * @param int $legajo
     * @param int $cargo
     *
     * @return array{dias: int, horas: int}
     */
    public function getHorasYDias(int $legajo, int $cargo): array
    {
        return Dh21::query()
            ->where('nro_legaj', $legajo)
            ->where('nro_cargo', $cargo)
            ->where('codn_conce', -51)
            ->select([
                DB::raw('MAX(nov1_conce) as dias'),
                DB::raw('MAX(nov2_conce) as horas'),
            ])
            ->first()
            ->toArray();
    }

    /**
     * Obtiene las liquidaciones con sus importes y descripciones.
     *
     * @param array $conditions Condiciones adicionales para filtrar
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLiquidaciones(array $conditions = []): Collection
    {
        return Dh21::query()
            ->select('dh22.desc_liqui', 'dh21.codn_conce', 'dh21.impp_conce', 'dh21.desc_conce')
            ->with('dh22')
            ->when(!empty($conditions), function ($query) use ($conditions): void {
                foreach ($conditions as $column => $value) {
                    $query->where($column, $value);
                }
            })
            ->when(empty($conditions), function ($query): void {
                $query->where('nro_legaj', '=', 1);
            })
            ->get();
    }

    /**
     * Obtiene conceptos liquidados para procesamiento SICOSS.
     *
     * @param int $per_anoct
     * @param int $per_mesct
     * @param string $where
     *
     * @return array
     */
    public function obtenerConceptosLiquidadosSicoss(int $per_anoct, int $per_mesct, string $where): array
    {
        // Se hace una consulta de todos los conceptos liquidados del periodo vigente, que generen impuestos cuyos conceptos sean mayores a cero
        // Se arma una columna tipos_grupos que va tener todos los numeros tipo de grupo a los cuales pertenece el concepto
        // Se guardan los datos en una tabla temporal: pre_conceptos_liquidados

        $sql_conceptos_liq = "
                SELECT
                        --LIQUIDACION
                        DISTINCT(liquidaciones.id_liquidacion),
                        liquidaciones.impp_conce,
                        liquidaciones.ano_retro,
                        liquidaciones.mes_retro,
                        --LEGAJO
                        dh01.nro_legaj,
                        --CONCEPTOS
                        liquidaciones.codn_conce,
                        liquidaciones.tipo_conce,
                        liquidaciones.nro_cargo,
                        liquidaciones.nov1_conce,
                        dh12.nro_orimp,
                        -- obtengo un arreglo de los tipos de grupo a los que pertenece el concepto
                        ( SELECT
                                ( SELECT
                                        array(
                                                SELECT
                                                        distinct codn_tipogrupo
                                                FROM
                                                        mapuche.dh15
                                                WHERE
                                                        dh15.codn_grupo IN (SELECT
                                                                                    codn_grupo
                                                                            FROM
                                                                                    mapuche.dh16
                                                                            WHERE
                                                                                    dh16.codn_conce=liquidaciones.codn_conce
                                                                          )
                                             )
                                )
                         ) AS tipos_grupos,
                         liquidaciones.codigoescalafon as codigoescalafon

                INTO TEMP
                         pre_conceptos_liquidados
                FROM
                        (
                            -- UNION de liquidaciones actuales e históricas
                            SELECT id_liquidacion, impp_conce, ano_retro, mes_retro, nro_legaj,
                                   codn_conce, tipo_conce, nro_cargo, nov1_conce, nro_liqui, codigoescalafon
                            FROM mapuche.dh21
                            UNION ALL
                            SELECT id_liquidacion, impp_conce, ano_retro, mes_retro, nro_legaj,
                                   codn_conce, tipo_conce, nro_cargo, nov1_conce, nro_liqui, codigoescalafon
                            FROM mapuche.dh21h
                        ) as liquidaciones
                        LEFT OUTER JOIN mapuche.dh01 ON (dh01.nro_legaj  = liquidaciones.nro_legaj)       -- Agentes
                        LEFT OUTER JOIN mapuche.dh12 ON (liquidaciones.codn_conce = dh12.codn_conce)      -- Conceptos de Liquidaci�n
                        LEFT OUTER JOIN mapuche.dh16 ON (dh16.codn_conce = dh12.codn_conce)              -- Grupo al que pertenecen los conceptos
                        LEFT OUTER JOIN mapuche.dh22 ON (liquidaciones.nro_liqui  = dh22.nro_liqui)      -- Par�metros de Liquidaciones
                WHERE
                        -- liquidaciones del periodo vigente y que generen impuestos
                        dh22.per_liano = ? AND  dh22.per_limes = ?
                        AND dh22.sino_genimp
                        -- AND dh22.nro_liqui = 31
                        AND liquidaciones.codn_conce > 0
                        --AND liquidaciones.nro_orimp > 0
                        AND {$where}
            ";
        // Ejecutar la creación de la tabla temporal
        DB::connection($this->getConnectionName())->statement($sql_conceptos_liq, [$per_anoct, $per_mesct]);

        // Crear índice en la tabla temporal
        DB::connection($this->getConnectionName())->statement(
            'CREATE INDEX ix_pre_conceptos_liquidados_1 ON pre_conceptos_liquidados(id_liquidacion);',
        );

        // Obtener los datos de la tabla temporal
        $resultados = DB::connection($this->getConnectionName())
            ->select('SELECT * FROM pre_conceptos_liquidados');

        // Convertir los resultados a array
        return array_map(function ($item) {
            return (array)$item;
        }, $resultados);
    }

    /**
     * Obtiene períodos retro disponibles de la tabla temporal pre_conceptos_liquidados.
     *
     * @param bool $check_lic
     * @param bool $check_retr
     *
     * @return array
     */
    public function obtenerPeriodosRetro(bool $check_lic = false, bool $check_retr = false): array
    {
        // Obtengo los año y mes retro disponibles del periodo de la tabla temporal que genero para hacer el join con la tabla de legajos
        $rs_periodos_retro = [];

        if ($check_lic && $check_retr) {
            $sql_periodos_retro = '
                                    SELECT
                                            DISTINCT(ano_retro),mes_retro
                                    FROM
                                            pre_conceptos_liquidados
                                    ORDER BY
                                            ano_retro desc, mes_retro desc
                                    ';
            $rs_periodos_retro = DB::connection($this->getConnectionName())->select($sql_periodos_retro);
            $temp['ano_retro'] = '0';
            $temp['mes_retro'] = '0';
            array_push($rs_periodos_retro, $temp);
        } elseif (!$check_lic) {
            $sql_periodos_retro = '
                                    SELECT
                                            DISTINCT(ano_retro),mes_retro
                                    FROM
                                            pre_conceptos_liquidados
                                    ORDER BY
                                            ano_retro desc, mes_retro desc
                                    ';
            $rs_periodos_retro = DB::connection($this->getConnectionName())->select($sql_periodos_retro);
        }
        // si tiene check de licencias solo debo tener en cuenta el periodo retro 0-0
        else {
            $temp['ano_retro'] = '0';
            $temp['mes_retro'] = '0';
            array_push($rs_periodos_retro, $temp);
        }

        return $rs_periodos_retro;
    }
}
