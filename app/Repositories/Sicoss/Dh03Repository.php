<?php

namespace App\Repositories\Sicoss;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        return DB::connection(MapucheConfig::getStaticConnectionName())->select($sql);
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

        return DB::connection(MapucheConfig::getStaticConnectionName())->select($sql);
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

        return DB::connection(MapucheConfig::getStaticConnectionName())->select($sql);
    }

    /**
     * Verifica si un vínculo es válido para una fecha específica.
     *
     * Un vínculo se considera válido si:
     * - Existe en la tabla dh03
     * - La fecha proporcionada coincide con el día siguiente a la fecha de baja
     * - No existe más de un registro relacionado en la tabla dh10
     *
     * @param string $fecha Fecha a verificar en formato compatible con PostgreSQL
     * @param int $vinculo Número de vínculo a validar
     * @return bool True si el vínculo es válido, False en caso contrario
     */
    public static function esVinculoValido(string $fecha, int $vinculo): bool
    {
        try {
            $sql = "
                SELECT
                    COUNT(*) as cantidad
                FROM
                    mapuche.dh03 vinculo
                WHERE
                    vinculo.nro_cargo = ? AND
                    ? = vinculo.fec_baja+1 AND
                    NOT EXISTS (
                        SELECT
                            vinculo.vcl_cargo
                        FROM
                            mapuche.dh10 vinculo
                        WHERE
                            vinculo.vcl_cargo = ? AND
                            vinculo.nro_cargo != vinculo.vcl_cargo
                        GROUP BY
                            vinculo.vcl_cargo
                        HAVING count(*) > 1
                    )
            ";
            
            $result = DB::connection(MapucheConfig::getStaticConnectionName())
                       ->selectOne($sql, [$vinculo, $fecha, $vinculo]);
                       
            return $result->cantidad > 0;
        } catch (Exception $e) {
            Log::error('Error al verificar vínculo válido: ' . $e->getMessage(), [
                'fecha' => $fecha,
                'vinculo' => $vinculo
            ]);
            return false;
        }
    }

    /**
     * Verifica si existe una categoría diferencial activa para un legajo específico.
     * 
     * Consulta la tabla dh03 para determinar si un legajo tiene asignada alguna de las 
     * categorías diferenciales especificadas y si el cargo está activo según la función
     * map_es_cargo_activo de PostgreSQL.
     *
     * @param int $nroLegajo Número de legajo a consultar
     * @param string|array $catDiferencial Categorías diferenciales (string separado por comas o array)
     * @return bool True si existe al menos una categoría diferencial activa, false en caso contrario
     */
    public function existeCategoriaDiferencial(int $nroLegajo, string|array $catDiferencial): bool
    {
        try {
            // Procesamiento de categorías: convertir string a array si es necesario
            if (is_string($catDiferencial)) {
                $categorias = array_map('trim', explode("','", trim($catDiferencial, "'")));
            } else {
                $categorias = $catDiferencial;
            }

            // Construcción de la consulta SQL con parámetros seguros
            $placeholders = str_repeat('?,', count($categorias) - 1) . '?';
            
            $sql = "SELECT COUNT(*) as total 
                    FROM mapuche.dh03 
                    WHERE codc_categ IN ($placeholders) 
                    AND nro_legaj = ? 
                    AND map_es_cargo_activo(nro_cargo)";

            // Preparación de parámetros para la consulta
            $params = array_merge($categorias, [$nroLegajo]);

            // Ejecución de la consulta
            $result = DB::connection(MapucheConfig::getStaticConnectionName())
                       ->selectOne($sql, $params);

            return $result->total > 0;

        } catch (Exception $e) {
            Log::error('Error al verificar categoría diferencial: ' . $e->getMessage(), [
                'legajo' => $nroLegajo,
                'categorias' => $catDiferencial
            ]);
            return false;
        }
    }
}
