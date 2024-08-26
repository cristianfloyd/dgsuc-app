<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'pgsql-mapuche';


    public function up()
    {
        DB::unprepared("
            CREATE OR REPLACE FUNCTION suc.get_mi_simplificacion_tt(
                nro_liqui_param INT,
                periodo_fiscal_param CHAR(6)
            )
            RETURNS TABLE(
                nro_legaj int4,
                nro_liqui int4,
                sino_cerra CHAR(1),
                desc_estado_liquidacion VARCHAR(50),
                nro_cargo int4,
                periodo_fiscal CHAR(6),
                tipo_de_registro CHAR(2),
                codigo_movimiento CHAR(2),
                cuil CHAR(11),
                trabajador_agropecuario CHAR(1),
                modalidad_contrato CHAR(3),
                inicio_rel_lab CHAR(10),
                fin_rel_lab CHAR(10),
                obra_social CHAR(6),
                codigo_situacion_baja CHAR(2),
                fecha_tel_renuncia CHAR(10),
                retribucion_pactada CHAR(15),
                modalidad_liquidacion CHAR(1),
                domicilio CHAR(5),
                actividad CHAR(6),
                puesto CHAR(4),
                rectificacion CHAR(2),
                ccct CHAR(10),
                tipo_servicio CHAR(3),
                categoria CHAR(6),
                fecha_susp_servicios_temp CHAR(10),
                nro_form_agrop CHAR(10),
                covid CHAR(1)
            ) AS $$
            WITH MinCargos AS (
                SELECT
                    d.nro_legaj,
                    d.nro_cargo,
                    d3.fec_alta AS inicio_rel_lab,
                    ROW_NUMBER() OVER (PARTITION BY d.nro_legaj ORDER BY d3.fec_alta) AS rn
                FROM
                    mapuche.dh21 d
                INNER JOIN mapuche.dh03 d3
                    ON d.nro_cargo = d3.nro_cargo
                WHERE
                    d.tipo_conce = 'C'
                    AND d.nro_liqui = nro_liqui_param
                    AND d3.chkstopliq = 0
            )
            SELECT
                d.nro_legaj,
                d4.nro_liqui,
                d4.sino_cerra,
                el.desc_estado_liquidacion,
                d.nro_cargo,
                periodo_fiscal_param as periodo_fiscal,
                '01' AS tipo_de_registro,
                'AT' AS codigo_movimiento,
                CONCAT(d2.nro_cuil1, d2.nro_cuil, d2.nro_cuil2) AS cuil,
                'N' AS trabajador_agropecuario,
                '008' AS modalidad_contrato,
                MinCargos.inicio_rel_lab,
                d3.fec_baja AS fin_rel_lab,
                '000000' AS obra_social,
                '01' AS codigo_situacion_baja,
                '0000000000' AS fecha_tel_renuncia,
                TO_CHAR(ROUND(SUM(d.impp_conce)::NUMERIC , 2), 'FM999999999999.00') AS retribucion_pactada,
                1 AS modalidad_liquidaicon,
                d3.codc_uacad AS domicilio,
                NULL AS actividad,
                NULL AS puesto,
                '00' AS rectificacion,
                '9999/99' AS ccct,
                '000' AS tipo_servicio,
                '000000' AS categoria,
                NULL AS fecha_susp_servicios,
                '0000000000' AS nro_form_agrop,
                '0' AS covid
            FROM
                mapuche.dh21 d
            INNER JOIN mapuche.dh01 d2
                ON d.nro_legaj = d2.nro_legaj
            INNER JOIN mapuche.dh03 d3
                ON d.nro_cargo = d3.nro_cargo
            INNER JOIN mapuche.dh22 d4
                ON d.nro_liqui = d4.nro_liqui
            INNER JOIN mapuche.estado_liquidacion el
                ON d4.sino_cerra = el.cod_estado_liquidacion
            INNER JOIN MinCargos
                ON d.nro_legaj = MinCargos.nro_legaj
                AND d.nro_cargo = MinCargos.nro_cargo
            INNER JOIN suc.tabla_temp_cuils tc
                ON CONCAT(d2.nro_cuil1, d2.nro_cuil, d2.nro_cuil2) = tc.cuil
            WHERE
                d.tipo_conce = 'C'
                AND d.nro_liqui = nro_liqui_param
                AND d3.chkstopliq = 0
                AND MinCargos.rn = 1
            GROUP BY
                d.nro_legaj,
                d4.nro_liqui,
                d4.sino_cerra,
                el.desc_estado_liquidacion,
                d.nro_cargo,
                CONCAT(d2.nro_cuil1, d2.nro_cuil, d2.nro_cuil2),
                d3.fec_baja,
                d3.codc_uacad,
                MinCargos.inicio_rel_lab
            ORDER BY
                d.nro_legaj,
                d.nro_cargo;
            $$ LANGUAGE sql STABLE;
        ");
    }

    public function down()
    {
        DB::unprepared('DROP FUNCTION IF EXISTS suc.get_mi_simplificacion_tt');
    }
}
;
