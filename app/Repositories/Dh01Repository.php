<?php

namespace App\Repositories;

use App\Traits\MapucheConnectionTrait;
use App\Contracts\Dh01RepositoryInterface;

class Dh01Repository implements Dh01RepositoryInterface
{
    use MapucheConnectionTrait;

    /**
     * Obtiene SQL parametrizado para consultar legajos con sus datos relacionados
     *
     * @param string $tabla Tabla base para la consulta (conceptos_liquidados o mapuche.dh01)
     * @param int $valor Valor para el campo licencia
     * @param string $where Condición WHERE adicional
     * @param string $codc_reparto Código de reparto para la condición de régimen
     * @return string SQL query string
     */
    public function getSqlLegajos(string $tabla, int $valor, string $where = 'true', string $codc_reparto = ''): string
    {
        $vacio = "";
        $sql = "
                SELECT
                        DISTINCT(dh01.nro_legaj),
                        (dh01.nro_cuil1::char(2)||LPAD(dh01.nro_cuil::char(8),8,'0')||dh01.nro_cuil2::char(1))::float8 AS cuit,
                        dh01.desc_appat||' '||dh01.desc_nombr AS apyno,
                        dh01.tipo_estad AS estado,
                        (SELECT
                                COUNT(*)
                            FROM
                                mapuche.dh02
                            WHERE
                            dh02.nro_legaj = dh01.nro_legaj AND dh02.sino_cargo!='N' AND dh02.codc_paren ='CONY'
                        ) AS conyugue,
                        (SELECT
                                COUNT(*)
                        FROM
                                mapuche.dh02
                        WHERE
                                dh02.nro_legaj=dh01.nro_legaj AND dh02.sino_cargo!='N' AND dh02.codc_paren IN ('HIJO', 'HIJN', 'HINC' ,'HINN' )
                        ) AS hijos,
                        dha8.ProvinciaLocalidad,
                        dha8.codigosituacion,
                        dha8.CodigoCondicion,
                        dha8.codigozona,
                        dha8.CodigoActividad,
                        dha8.porcaporteadicss AS aporteAdicional,
                        dha8.trabajador_convencionado AS trabajadorconvencionado,
                        dha8.codigomodalcontrat AS codigocontratacion,
                        CASE WHEN ((dh09.codc_bprev = '$codc_reparto' ) OR (dh09.fuerza_reparto) OR (('$codc_reparto' = '{$vacio}') AND (dh09.codc_bprev IS NULL)))THEN '1'
                        ELSE '0'
                        END AS regimen,
                        dh09.cant_cargo AS adherentes,
                        {$valor} AS licencia,
                        0 AS importeimponible_9
                    FROM
                        {$tabla}
                        LEFT OUTER JOIN mapuche.dh02 ON dh02.nro_legaj = {$tabla}.nro_legaj
                        LEFT OUTER JOIN mapuche.dha8 ON dha8.nro_legajo = {$tabla}.nro_legaj
                        LEFT OUTER JOIN mapuche.dh09 ON dh09.nro_legaj = {$tabla}.nro_legaj
                        LEFT OUTER JOIN mapuche.dhe9 ON dhe9.nro_legaj = {$tabla}.nro_legaj ";

        // Si la tabla es dh01 no necesito el join con la misma tabla
        if ($tabla != "mapuche.dh01") {
            $sql .= " LEFT OUTER JOIN mapuche.dh01 ON {$tabla}.nro_legaj = dh01.nro_legaj ";
        }

        $sql .= " WHERE {$where}";

        return $sql;
    }
}
