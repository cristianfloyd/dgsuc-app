<?php

namespace App\Repositories\Sicoss;

use App\Models\Dh01;
use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use App\Repositories\Sicoss\Contracts\SicossCalculoRepositoryInterface;

class SicossCalculoRepository implements SicossCalculoRepositoryInterface
{
    use MapucheConnectionTrait;

    /**
     * Sumariza importes de conceptos que pertenecen a un determinado tipo de concepto
     * Se podía hacer en la función sumarizar_conceptos_por_tipos_grupos pero queda más claro separado
     *
     * @param int $nro_legajo
     * @param string $tipo
     * @param string $where
     * @return float
     */
    public function calcularRemunerGrupo(int $nro_legajo, string $tipo, string $where): float
    {
        $sql = "
                SELECT
                    SUM(conceptos_liquidados.impp_conce::numeric(10,2)) AS suma
                FROM
                    conceptos_liquidados
                WHERE
                    nro_legaj = {$nro_legajo}
                    AND tipo_conce = '{$tipo}'
                    AND {$where}
        ";

        $suma = DB::connection($this->getConnectionName())->select($sql);
        return (float)($suma[0]['suma'] ?? 0);
    }

    /**
     * Calcula horas extras por concepto y cargo
     *
     * @param int $concepto
     * @param int $cargo
     * @return array
     */
    public function calculoHorasExtras(int $concepto, int $cargo): array
    {
        $sql = "SELECT
                        cargo,concepto,sum(nov1) as sum_nov1
                    FROM (
                            SELECT
                                dh21.nro_cargo,dh21.codn_conce,	sum(t.nov1_conce)/(SELECT
                                                                                        COUNT(*)
                                                                                    FROM
                                                                                         mapuche.dh24
                                                                                    WHERE
                                                                                           nro_cargo={$cargo}
                                                                                    ) as horas, t.id_liquidacion
                            FROM
                                 pre_conceptos_liquidados t
                            LEFT JOIN
                                     mapuche.dh21 dh21 ON (t.id_liquidacion=dh21.id_liquidacion)
                            GROUP BY
                                    dh21.nro_cargo,dh21.codn_conce,dh21.nro_liqui, dh21.nro_legaj,
                                    dh21.codn_progr, dh21.codn_subpr,dh21.codn_proye,dh21.codn_activ,dh21.codn_obra,dh21.codn_fuent,
                                    dh21.codn_area,dh21.codn_subar,dh21.codn_final,dh21.codn_funci,dh21.codn_grupo_presup,dh21.tipo_ejercicio,
                                    dh21.codn_subsubar, t.id_liquidacion
                            ) t1(cargo,concepto,nov1)
                    WHERE
                        cargo={$cargo} AND concepto={$concepto}
                    GROUP BY
                        cargo,concepto
                    ORDER BY
                        cargo,concepto";

        $horas = DB::connection($this->getConnectionName())->select($sql);
        return !empty($horas) ? (array)$horas[0] : [];
    }

    /**
     * Se obtienen los importes de otra actividad, cuando tiene varias tomo la del último periodo
     *
     * @param int $nro_legajo
     * @return array
     */
    public function otraActividad(int $nro_legajo): array
    {
        $sql = "
                SELECT
                    importe AS ImporteBrutoOtraActividad,
                    importe_sac AS ImporteSACOtraActividad
                FROM
                    mapuche.dhe9
                WHERE
                    nro_legaj = {$nro_legajo}
                ORDER BY
                    vig_ano, vig_mes DESC
                LIMIT 1
            ";

        $resp = DB::connection($this->getConnectionName())->select($sql);

        if (empty($resp)) {
            return [
                'importesacotraactividad' => 0,
                'importebrutootraactividad' => 0
            ];
        }

        return (array)$resp[0];
    }

    /**
     * Devuelve el código DGI de obra social correspondiente dado un legajo
     *
     * @param int $nro_legajo
     * @return string
     */
    public function codigoOs(int $nro_legajo): string
    {
        // Si es jubilado directamente retorno 000000.
        if (Dh01::esJubilado($nro_legajo)) {
            return '000000';
        }

        // Si no es jubilado me fijo en el campo de dh09
        $sql = "
                SELECT
                    dh09.codc_obsoc
                FROM
                    mapuche.dh09
                WHERE
                    dh09.nro_legaj = {$nro_legajo}
            ";

        $siglas = DB::connection($this->getConnectionName())->select($sql);

        $sigla = '';
        if (empty($siglas[0]['codc_obsoc'])) {
            // Si campo dh09 está vacío asigno la obra social por defecto
            $sigla = app(\App\Models\Mapuche\MapucheConfig::class)::getDefaultsObraSocial();
        } else {
            $sigla = $siglas[0]['codc_obsoc'];
        }

        // Obtengo el código DGI con esa sigla
        $sql2 = "
                SELECT
                    dh37.codn_osdgi
                FROM
                    mapuche.dh37
                WHERE
                    dh37.codc_obsoc = '{$sigla}'
            ";

        $coddgi = DB::connection($this->getConnectionName())->select($sql2);

        if (empty($coddgi[0]['codn_osdgi'])) {
            return '000000';
        }

        return $coddgi[0]['codn_osdgi'];
    }
}
