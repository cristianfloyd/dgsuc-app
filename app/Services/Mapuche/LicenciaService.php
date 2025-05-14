<?php

namespace App\Services\Mapuche;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Mapuche\MapucheConfig;
use App\Traits\MapucheConnectionTrait;
use Spatie\LaravelData\DataCollection;
use App\Data\Responses\LicenciaVigenteData;

class LicenciaService
{
    use MapucheConnectionTrait;

    /**
     * Nombre del esquema de la base de datos Mapuche
     *
     * @var string
     */
    protected string $esquema;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->esquema = 'mapuche';
    }

    /**
     * Obtiene las licencias vigentes para los legajos indicados.
     *
     * @param array $legajos Array de números de legajo
     * @return DataCollection
     */
    public function getLicenciasVigentes(array $legajos): DataCollection
    {
        // 1. Preparar los parámetros para la consulta
        $fechaInicio = MapucheConfig::getFechaInicioPeriodoCorriente();
        $fechaFin = MapucheConfig::getFechaFinPeriodoCorriente();

        // 2. Obtener variantes de licencias desde MapucheConfig
        $variantesIltPrimerTramo = MapucheConfig::getVarLicencias10Dias();
        $variantesIltSegundoTramo = MapucheConfig::getVarLicencias11DiasSiguientes();
        $variantesDown = MapucheConfig::getVarLicenciasMaternidadDown();
        $variantesExcedencia = MapucheConfig::getVarLicenciaExcedencia();
        $variantesVacaciones = MapucheConfig::getVarLicenciaVacaciones();
        $variantesProtecIntegral = MapucheConfig::getVarLicenciaProtecIntegral();

        // 3. Preparar condiciones de la consulta SQL
        $whereLegajos = 'dh01.nro_legaj IN (' . implode(',', $legajos) . ')';

        // Preparar condiciones para variantes ILT
        $sqlIlt = '';
        $whereIlt = '';

        if (!empty($variantesIltPrimerTramo)) {
            $sqlIlt = " OR ( dh05.nrovarlicencia IN ($variantesIltPrimerTramo)) ";
            $whereIlt = " WHEN dh05.nrovarlicencia IN ($variantesIltPrimerTramo) THEN '18'::integer ";
        }

        if (!empty($variantesIltSegundoTramo)) {
            $sqlIlt .= " OR ( dh05.nrovarlicencia IN ($variantesIltSegundoTramo)) ";
            $whereIlt .= " WHEN dh05.nrovarlicencia IN ($variantesIltSegundoTramo) THEN '19'::integer ";
        }

        // Preparar condiciones para otras variantes
        $whereDown = !empty($variantesDown) ? " WHEN dh05.nrovarlicencia IN ($variantesDown) THEN '11'::integer " : '';
        $whereExcedencia = !empty($variantesExcedencia) ? " WHEN dh05.nrovarlicencia IN ($variantesExcedencia) THEN '10'::integer " : '';
        $whereVacaciones = !empty($variantesVacaciones) ? " WHEN dh05.nrovarlicencia IN ($variantesVacaciones) THEN '12'::integer " : '';
        $whereProtecIntegral = !empty($variantesProtecIntegral) ? " WHEN dh05.nrovarlicencia IN ($variantesProtecIntegral) THEN '51'::integer " : '';

        // 4. Condición para licencias no remuneradas
        $whereNorem = "(dl02.es_maternidad IS TRUE OR
                        (NOT dl02.es_remunerada OR
                            (dl02.es_remunerada AND dl02.porcremuneracion = '0')
                        )
                        $sqlIlt
                    )";

        // 5. Construir la consulta SQL completa
        $sql = "SELECT
                    dh01.nro_legaj,
                    CASE
                        WHEN dh05.fec_desde <= ?::date THEN date_part('day', ?::timestamp)::integer
                        ELSE date_part('day', dh05.fec_desde::timestamp)
                    END AS inicio,
                    CASE
                        WHEN dh05.fec_hasta > ?::date OR dh05.fec_hasta IS NULL THEN date_part('day', ?::timestamp)::integer
                        ELSE date_part('day', dh05.fec_hasta::timestamp)
                    END AS final,
                    TRUE AS es_legajo,
                    CASE
                        $whereDown
                        WHEN dl02.es_maternidad THEN '5'::integer
                        $whereExcedencia
                        $whereIlt
                        $whereVacaciones
                        $whereProtecIntegral
                        ELSE '13'::integer
                    END AS condicion,
                    dl02.observacion as descripcion_licencia,
                    dh05.fec_desde,
                    dh05.fec_hasta,
                    NULL as nro_cargo
                FROM
                    {$this->esquema}.dh05
                    LEFT OUTER JOIN {$this->esquema}.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
                    LEFT OUTER JOIN {$this->esquema}.dh01 ON (dh05.nro_legaj = dh01.nro_legaj)
                WHERE
                    dh05.nro_legaj IS NOT NULL
                    AND (fec_desde <= ?::date AND (fec_hasta is null OR fec_hasta >= ?::date))
                    AND suc.map_es_licencia_vigente(dh05.nro_licencia)
                    AND $whereNorem
                    AND $whereLegajos

                UNION

                SELECT
                    dh01.nro_legaj,
                    CASE
                        WHEN dh05.fec_desde <= ?::date THEN date_part('day', ?::timestamp)::integer
                        ELSE date_part('day', dh05.fec_desde::timestamp)
                    END AS inicio,
                    CASE
                        WHEN dh05.fec_hasta > ?::date OR dh05.fec_hasta IS NULL THEN date_part('day', ?::timestamp)::integer
                        ELSE date_part('day', dh05.fec_hasta::timestamp)
                    END AS final,
                    FALSE AS es_legajo,
                    CASE
                        $whereDown
                        WHEN dl02.es_maternidad THEN '5'::integer
                        $whereExcedencia
                        $whereIlt
                        $whereVacaciones
                        $whereProtecIntegral
                        ELSE '13'::integer
                    END AS condicion,
                    dl02.observacion as descripcion_licencia,
                    dh05.fec_desde,
                    dh05.fec_hasta,
                    dh05.nro_cargo
                FROM
                    {$this->esquema}.dh05
                    LEFT OUTER JOIN {$this->esquema}.dh03 ON (dh03.nro_cargo = dh05.nro_cargo)
                    LEFT OUTER JOIN {$this->esquema}.dh01 ON (dh03.nro_legaj = dh01.nro_legaj)
                    LEFT OUTER JOIN {$this->esquema}.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
                WHERE
                    dh05.nro_cargo IS NOT NULL
                    AND (fec_desde <= ?::date AND (fec_hasta is null OR fec_hasta >= ?::date))
                    AND {$this->esquema}.map_es_cargo_activo(dh05.nro_cargo)
                    AND suc.map_es_licencia_vigente(dh05.nro_licencia)
                    AND $whereNorem
                    AND $whereLegajos";

        // 6. Ejecutar la consulta con los parámetros correctos
        $params = [
            $fechaInicio, $fechaInicio, $fechaFin, $fechaFin, $fechaFin, $fechaInicio,
            $fechaInicio, $fechaInicio, $fechaFin, $fechaFin, $fechaFin, $fechaInicio
        ];

        $resultados = DB::connection($this->getConnectionName())->select($sql, $params);

        // 7. Convertir resultados a DTO
        return LicenciaVigenteData::fromResultados($resultados);
    }

    /**
     * Obtiene las licencias vigentes para un legajo específico.
     *
     * @param int $legajo Número de legajo
     * @return DataCollection
     */
    public function getLicenciasVigentesPorLegajo(int $legajo): DataCollection
    {
        return $this->getLicenciasVigentes([$legajo]);
    }
}
