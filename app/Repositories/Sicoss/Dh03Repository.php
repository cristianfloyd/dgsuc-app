<?php

namespace App\Repositories\Sicoss;

use Illuminate\Support\Facades\DB;
use App\Models\Mapuche\MapucheConfig;
use App\Traits\MapucheConnectionTrait;
use App\Repositories\Sicoss\Contracts\Dh03RepositoryInterface;

class Dh03Repository implements Dh03RepositoryInterface
{
    use MapucheConnectionTrait;

    /**
     * Obtiene cargos activos sin licencia para un legajo
     *
     * @param int $legajo
     * @return array
     */
    public function getCargosActivosSinLicencia(int $legajo): array
    {
        $fecha_inicio = MapucheConfig::getFechaInicioPeriodoCorriente();
        $fecha_fin = MapucheConfig::getFechaFinPeriodoCorriente();

        $sql = "SELECT
                    dh03.nro_cargo,
                    CASE
                          WHEN fec_alta <= '$fecha_inicio'::date THEN date_part('day', timestamp '$fecha_inicio')::integer
                          ELSE date_part('day', fec_alta::timestamp)
                    END AS inicio,
                    CASE
                        WHEN fec_baja > '$fecha_fin'::date OR fec_baja IS NULL THEN date_part('day', timestamp '$fecha_fin')::integer
                          ELSE date_part('day', fec_baja::timestamp)
                    END AS final
                FROM
                    mapuche.dh03 dh03
                WHERE
                    (fec_baja IS NULL OR fec_baja >= '$fecha_inicio'::date) and
                    dh03.nro_legaj = $legajo and
                    dh03.nro_cargo NOT IN ( SELECT
                                                dh05.nro_cargo
                                            FROM
                                                mapuche.dh05
                                                JOIN mapuche.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
                                            WHERE
                                                dh05.nro_cargo = dh03.nro_cargo AND
                                                mapuche.map_es_licencia_vigente(dh05.nro_licencia) AND
                                                (dl02.es_maternidad IS TRUE OR (NOT dl02.es_remunerada OR (dl02.es_remunerada AND dl02.porcremuneracion = '0')))
                                                )
                ;";

        return DB::select($sql);
    }

    /**
     * Obtiene cargos activos con licencia vigente para un legajo
     *
     * @param int $legajo
     * @return array
     */
    public function getCargosActivosConLicenciaVigente(int $legajo): array
    {
        $fecha_inicio = MapucheConfig::getFechaInicioPeriodoCorriente();
        $fecha_fin = MapucheConfig::getFechaFinPeriodoCorriente();

        $sql = "SELECT
                    dh03.nro_cargo,
                    CASE
                        WHEN fec_alta <= '$fecha_inicio'::date THEN date_part('day', timestamp '$fecha_inicio')::integer
                        ELSE date_part('day', fec_alta::timestamp)
                    END AS inicio,
                    CASE
                            WHEN fec_baja > '$fecha_fin'::date OR fec_baja IS NULL THEN date_part('day', timestamp '$fecha_fin')::integer
                            ELSE date_part('day', fec_baja::timestamp)
                    END AS final,

                    CASE
                        WHEN fec_desde <= '$fecha_inicio'::date THEN date_part('day', timestamp '$fecha_inicio')::integer
                        ELSE date_part('day', fec_desde::timestamp)
                    END AS inicio_lic,
                    CASE
                            WHEN fec_hasta > '$fecha_fin'::date OR fec_hasta IS NULL THEN date_part('day', timestamp '$fecha_fin')::integer
                            ELSE date_part('day', fec_hasta::timestamp)
                    END AS final_lic,
                    CASE
                      WHEN dl02.es_maternidad THEN '5'::integer
                      ELSE
                        CASE
                            WHEN dl02.es_remunerada THEN '1'::integer
                            ELSE '13'::integer
                            END
                    END AS condicion

                FROM
                    mapuche.dh03 dh03,
                    mapuche.dh05 dh05
                LEFT OUTER JOIN mapuche.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
                WHERE
                    dh05.nro_cargo = dh03.nro_cargo AND
                    (fec_baja IS NULL OR fec_baja >= '$fecha_inicio'::date) AND
                    (fec_desde <= '$fecha_fin'::date AND fec_hasta >= '$fecha_inicio'::date) AND
                    dh03.nro_legaj = $legajo AND
                    dh03.nro_cargo NOT IN ( SELECT	dh05.nro_cargo
                                            FROM
                                                mapuche.dh05
                                            JOIN mapuche.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
                                            WHERE
                                                dh05.nro_cargo = dh03.nro_cargo AND
                                                mapuche.map_es_licencia_vigente(dh05.nro_licencia)
                                                AND (dh05.fec_desde < mapuche.map_get_fecha_inicio_periodo() - 1)  AND
                                                (dl02.es_maternidad IS TRUE OR (NOT dl02.es_remunerada OR (dl02.es_remunerada AND dl02.porcremuneracion = '0')))
                                            )
                ;";

        return DB::select($sql);
    }

    /**
     * Obtiene los límites de cargos para un legajo
     *
     * @param int $legajo
     * @return array
     */
    public function getLimitesCargos(int $legajo): array
    {
        $fecha_inicio = MapucheConfig::getFechaInicioPeriodoCorriente();
        $fecha_fin = MapucheConfig::getFechaFinPeriodoCorriente();

        $sql = "SELECT
                    CASE
                        WHEN MIN(fec_alta) > '$fecha_inicio'::date THEN date_part('day', MIN(fec_alta)::timestamp)
                        ELSE date_part('day', timestamp '$fecha_inicio')::integer
                    END AS minimo,
                    MAX(CASE
                        WHEN fec_baja > '$fecha_fin'::date OR
                             fec_baja IS NULL THEN date_part('day', timestamp '$fecha_fin')::integer
                        ELSE date_part('day', fec_baja::timestamp)
                    END) AS maximo
                FROM
                    mapuche.dh03
                WHERE
                    (fec_baja IS NULL OR fec_baja >= '$fecha_inicio'::date) AND
                    nro_legaj = $legajo
                ;";

        return DB::connection($this->getConnectionName())->select($sql);
    }
}
