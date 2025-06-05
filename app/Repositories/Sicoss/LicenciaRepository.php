<?php

namespace App\Repositories\Sicoss;

use Illuminate\Support\Facades\DB;
use App\Models\Mapuche\MapucheConfig;
use App\Traits\MapucheConnectionTrait;
use App\Repositories\Sicoss\Contracts\LicenciaRepositoryInterface;

class LicenciaRepository implements LicenciaRepositoryInterface
{
    use MapucheConnectionTrait;

    /**
     * Obtiene licencias de protección integral y vacaciones
     *
     * @param string $where_legajos
     * @return array
     */
    public function getLicenciasProtecintegralVacaciones(string $where_legajos): array
    {
        $fecha_inicio = MapucheConfig::getFechaInicioPeriodoCorriente();
        $fecha_fin = MapucheConfig::getFechaFinPeriodoCorriente();

        $variantes_vacaciones = MapucheConfig::getVarLicenciaVacaciones();
        $variantes_protecintegral = MapucheConfig::getVarLicenciaProtecIntegral();

        if ($variantes_vacaciones == '' && $variantes_protecintegral == '') {
            return [];
        } else if ($variantes_vacaciones != '' && $variantes_protecintegral != '') {
            $where_vacaciones = " WHEN dh05.nrovarlicencia IN ($variantes_vacaciones) THEN '12'::integer ";
            $where_protecintegral = " WHEN dh05.nrovarlicencia IN ($variantes_protecintegral) THEN '51'::integer ";
            $variantes = $variantes_vacaciones . ',' . $variantes_protecintegral;
            $where_legajos .= "AND dh05.nrovarlicencia IN ($variantes)";
        } else {
            if ($variantes_vacaciones != '') {
                $where_vacaciones = " WHEN dh05.nrovarlicencia IN ($variantes_vacaciones) THEN '12'::integer ";
                $where_legajos .= "AND dh05.nrovarlicencia IN ($variantes_vacaciones)";
            } else if ($variantes_protecintegral != '') {
                $where_protecintegral = " WHEN dh05.nrovarlicencia IN ($variantes_protecintegral) THEN '51'::integer ";
                $where_legajos .= "AND dh05.nrovarlicencia IN ($variantes_protecintegral)";
            }
        }

        $sql = "SELECT
                    dh01.nro_legaj,
                    CASE
                          WHEN dh05.fec_desde <= $fecha_inicio::date THEN date_part('day', timestamp $fecha_inicio)::integer
                          ELSE date_part('day', dh05.fec_desde::timestamp)
                    END AS inicio,
                    CASE
                        WHEN dh05.fec_hasta > $fecha_fin::date OR dh05.fec_hasta IS NULL THEN date_part('day', timestamp $fecha_fin)::integer
                          ELSE date_part('day', dh05.fec_hasta::timestamp)
                    END AS final,
                    TRUE AS es_legajo,
                    CASE
                      WHEN dl02.es_maternidad THEN '5'::integer
                      $where_vacaciones
                      $where_protecintegral
                      ELSE '13'::integer
                    END AS condicion
                FROM
                    mapuche.dh05
                    LEFT OUTER JOIN mapuche.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
                    LEFT OUTER JOIN mapuche.dh01 ON (dh05.nro_legaj = dh01.nro_legaj)
                WHERE
                    dh05.nro_legaj IS NOT NULL
                    AND (fec_desde <= $fecha_fin::date AND (fec_hasta is null OR fec_hasta >= $fecha_inicio::date))
                    AND mapuche.map_es_licencia_vigente(dh05.nro_licencia)
                    AND dl02.es_remunerada = TRUE
                    AND $where_legajos

                UNION

                SELECT
                    dh01.nro_legaj,
                    CASE
                          WHEN dh05.fec_desde <= $fecha_inicio::date THEN date_part('day', timestamp $fecha_inicio)::integer
                          ELSE date_part('day', dh05.fec_desde::timestamp)
                    END AS inicio,
                    CASE
                        WHEN dh05.fec_hasta > $fecha_fin::date OR dh05.fec_hasta IS NULL THEN date_part('day', timestamp $fecha_fin)::integer
                          ELSE date_part('day', dh05.fec_hasta::timestamp)
                    END AS final,
                    FALSE AS es_legajo,
                    CASE
                      WHEN dl02.es_maternidad THEN '5'::integer
                      $where_vacaciones
                      $where_protecintegral
                      ELSE '13'::integer
                    END AS condicion
                FROM
                    mapuche.dh05
                    LEFT OUTER JOIN mapuche.dh03 ON (dh03.nro_cargo = dh05.nro_cargo)
                    LEFT OUTER JOIN mapuche.dh01 ON (dh03.nro_legaj = dh01.nro_legaj)
                    LEFT OUTER JOIN mapuche.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
                WHERE
                    dh05.nro_cargo IS NOT NULL
                    AND (fec_desde <= $fecha_fin::date AND (fec_hasta is null OR fec_hasta >= $fecha_inicio::date))
                    AND mapuche.map_es_cargo_activo(dh05.nro_cargo)
                    AND mapuche.map_es_licencia_vigente(dh05.nro_licencia)
                    AND dl02.es_remunerada = TRUE
                    AND $where_legajos
                ;";

        return DB::connection($this->getConnectionName())->select($sql);
    }

    /**
     * Obtiene licencias vigentes
     *
     * @param string $where_legajos
     * @return array
     */
    public function getLicenciasVigentes(string $where_legajos): array
    {
        $fecha_inicio = MapucheConfig::getFechaInicioPeriodoCorriente();
        $fecha_fin = MapucheConfig::getFechaFinPeriodoCorriente();

        $variantes_ilt_primer_tramo = MapucheConfig::getVarLicencias10Dias();
        $variantes_ilt_segundo_tramo = MapucheConfig::getVarLicencias11DiasSiguientes();

        $variantes_down = MapucheConfig::getVarLicenciasMaternidadDown();

        $variantes_excedencia = MapucheConfig::getVarLicenciaExcedencia();

        $variantes_vacaciones = MapucheConfig::getVarLicenciaVacaciones();

        $variantes_protecintegral = MapucheConfig::getVarLicenciaProtecIntegral();

        if (isset($variantes_ilt_primer_tramo) && !empty($variantes_ilt_primer_tramo)) {
            $sql_ilt = " OR ( dh05.nrovarlicencia IN ($variantes_ilt_primer_tramo)) ";
            $where_ilt = " WHEN dh05.nrovarlicencia IN ($variantes_ilt_primer_tramo) THEN '18'::integer ";
        } else {
            $sql_ilt = '';
            $where_ilt = '';
        }

        $where_down = '';
        $where_excedencia = '';
        $where_vacaciones = '';
        $where_protecintegral = '';

        if (isset($variantes_ilt_segundo_tramo) && !empty($variantes_ilt_segundo_tramo)) {
            $sql_ilt .= " OR ( dh05.nrovarlicencia IN ($variantes_ilt_segundo_tramo)) ";
            $where_ilt .= " WHEN dh05.nrovarlicencia IN ($variantes_ilt_segundo_tramo) THEN '19'::integer ";
        }

        if (isset($variantes_down) && !empty($variantes_down)) {
            $where_down = " WHEN dh05.nrovarlicencia IN ($variantes_down) THEN '11'::integer ";
        }

        if (isset($variantes_excedencia) && !empty($variantes_excedencia)) {
            $where_excedencia = " WHEN dh05.nrovarlicencia IN ($variantes_excedencia) THEN '10'::integer ";
        }

        if (isset($variantes_vacaciones) && !empty($variantes_vacaciones)) {
            $where_vacaciones = " WHEN dh05.nrovarlicencia IN ($variantes_vacaciones) THEN '12'::integer ";
        }

        if (isset($variantes_protecintegral) && !empty($variantes_protecintegral)) {
            $where_protecintegral = " WHEN dh05.nrovarlicencia IN ($variantes_protecintegral) THEN '51'::integer ";
        }

        $where_norem = "(dl02.es_maternidad IS TRUE OR
                               (NOT dl02.es_remunerada OR
                                   (dl02.es_remunerada AND dl02.porcremuneracion = '0')
                               )
                                $sql_ilt
                           )";

        $sql = "SELECT
                    dh01.nro_legaj,
                    CASE
                          WHEN dh05.fec_desde <= $fecha_inicio::date THEN date_part('day', timestamp $fecha_inicio)::integer
                          ELSE date_part('day', dh05.fec_desde::timestamp)
                    END AS inicio,
                    CASE
                        WHEN dh05.fec_hasta > $fecha_fin::date OR dh05.fec_hasta IS NULL THEN date_part('day', timestamp $fecha_fin)::integer
                          ELSE date_part('day', dh05.fec_hasta::timestamp)
                    END AS final,
                    TRUE AS es_legajo,
                    CASE
                      $where_down
                      WHEN dl02.es_maternidad THEN '5'::integer
                      $where_excedencia
                      $where_ilt
                      $where_vacaciones
                      $where_protecintegral
                      ELSE '13'::integer
                    END AS condicion
                FROM
                    mapuche.dh05
                    LEFT OUTER JOIN mapuche.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
                    LEFT OUTER JOIN mapuche.dh01 ON (dh05.nro_legaj = dh01.nro_legaj)
                WHERE
                    dh05.nro_legaj IS NOT NULL
                    AND (fec_desde <= $fecha_fin::date AND (fec_hasta is null OR fec_hasta >= $fecha_inicio::date))
                    AND mapuche.map_es_licencia_vigente(dh05.nro_licencia)
                    AND $where_norem
                    AND $where_legajos

                UNION

                SELECT
                    dh01.nro_legaj,
                    CASE
                          WHEN dh05.fec_desde <= $fecha_inicio::date THEN date_part('day', timestamp $fecha_inicio)::integer
                          ELSE date_part('day', dh05.fec_desde::timestamp)
                    END AS inicio,
                    CASE
                        WHEN dh05.fec_hasta > $fecha_fin::date OR dh05.fec_hasta IS NULL THEN date_part('day', timestamp $fecha_fin)::integer
                          ELSE date_part('day', dh05.fec_hasta::timestamp)
                    END AS final,
                    FALSE AS es_legajo,
                    CASE
                      $where_down
                      WHEN dl02.es_maternidad THEN '5'::integer
                      $where_excedencia
                      $where_ilt
                      $where_vacaciones
                      $where_protecintegral
                      ELSE '13'::integer
                    END AS condicion
                FROM
                    mapuche.dh05
                    LEFT OUTER JOIN mapuche.dh03 ON (dh03.nro_cargo = dh05.nro_cargo)
                    LEFT OUTER JOIN mapuche.dh01 ON (dh03.nro_legaj = dh01.nro_legaj)
                    LEFT OUTER JOIN mapuche.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
                WHERE
                    dh05.nro_cargo IS NOT NULL
                    AND (fec_desde <= $fecha_fin::date AND (fec_hasta is null OR fec_hasta >= $fecha_inicio::date))
                    AND mapuche.map_es_cargo_activo(dh05.nro_cargo)
                    AND mapuche.map_es_licencia_vigente(dh05.nro_licencia)
                    AND $where_norem
                    AND $where_legajos
                ;";

        return DB::connection($this->getConnectionName())->select($sql);
    }
}
