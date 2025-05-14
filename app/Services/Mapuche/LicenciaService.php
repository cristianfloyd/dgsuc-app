<?php

namespace App\Services\Mapuche;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        try {
            // Validar que existan legajos para consultar
            if (empty($legajos)) {
                Log::warning('Se intentó consultar licencias sin proporcionar legajos');
                return new DataCollection(LicenciaVigenteData::class, []);
            }

            // Obtener parámetros de fecha desde la configuración
            $fechaInicio = MapucheConfig::getFechaInicioPeriodoCorriente();
            $fechaFin = MapucheConfig::getFechaFinPeriodoCorriente();

            // Construir la consulta SQL
            $sql = $this->buildLicenciasQuery($legajos);

            // Parámetros para la consulta
            $params = $this->getQueryParams($fechaInicio, $fechaFin);

            // Ejecutar la consulta
            $resultados = DB::connection($this->getConnectionName())
                ->select($sql, $params);

            // Convertir resultados a DTO
            return LicenciaVigenteData::fromResultados($resultados);
        } catch (\Exception $e) {
            Log::error('Error al obtener licencias vigentes: ' . $e->getMessage(), [
                'legajos' => $legajos,
                'exception' => $e
            ]);

            // Retornar colección vacía en caso de error
            return new DataCollection(LicenciaVigenteData::class, []);
        }
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

    /**
     * Construye la consulta SQL para obtener licencias vigentes.
     *
     * @param array $legajos Array de números de legajo
     * @return string Consulta SQL
     */
    private function buildLicenciasQuery(array $legajos): string
    {
        // Condición para filtrar por legajos
        $whereLegajos = 'dh01.nro_legaj IN (' . implode(',', $legajos) . ')';

        // Obtener variantes de licencias desde la configuración
        $variantesConfig = $this->getVariantesLicencias();

        // Preparar condiciones para tipos de licencias
        $condicionesLicencias = $this->buildLicenciasConditions($variantesConfig);

        // Condición para licencias no remuneradas
        $whereNorem = $this->buildNoRemuneradasCondition($condicionesLicencias['sqlIlt']);

        // Construir la consulta SQL completa
        return $this->buildFullQuery(
            $whereLegajos,
            $whereNorem,
            $condicionesLicencias['whereDown'],
            $condicionesLicencias['whereExcedencia'],
            $condicionesLicencias['whereIlt'],
            $condicionesLicencias['whereVacaciones'],
            $condicionesLicencias['whereProtecIntegral']
        );
    }

    /**
     * Obtiene las variantes de licencias desde la configuración.
     *
     * @return array Variantes de licencias
     */
    private function getVariantesLicencias(): array
    {
        return [
            'iltPrimerTramo' => MapucheConfig::getVarLicencias10Dias(),
            'iltSegundoTramo' => MapucheConfig::getVarLicencias11DiasSiguientes(),
            'down' => MapucheConfig::getVarLicenciasMaternidadDown(),
            'excedencia' => MapucheConfig::getVarLicenciaExcedencia(),
            'vacaciones' => MapucheConfig::getVarLicenciaVacaciones(),
            'protecIntegral' => MapucheConfig::getVarLicenciaProtecIntegral()
        ];
    }

    /**
     * Construye las condiciones para los diferentes tipos de licencias.
     *
     * @param array $variantes Variantes de licencias
     * @return array Condiciones SQL para cada tipo de licencia
     */
    private function buildLicenciasConditions(array $variantes): array
    {
        // Inicializar variables
        $sqlIlt = '';
        $whereIlt = '';

        // Condiciones para ILT primer tramo
        if (!empty($variantes['iltPrimerTramo'])) {
            $sqlIlt = " OR ( dh05.nrovarlicencia IN ({$variantes['iltPrimerTramo']})) ";
            $whereIlt = " WHEN dh05.nrovarlicencia IN ({$variantes['iltPrimerTramo']}) THEN '18'::integer ";
        }

        // Condiciones para ILT segundo tramo
        if (!empty($variantes['iltSegundoTramo'])) {
            $sqlIlt .= " OR ( dh05.nrovarlicencia IN ({$variantes['iltSegundoTramo']})) ";
            $whereIlt .= " WHEN dh05.nrovarlicencia IN ({$variantes['iltSegundoTramo']}) THEN '19'::integer ";
        }

        // Preparar condiciones para otras variantes
        $whereDown = !empty($variantes['down'])
            ? " WHEN dh05.nrovarlicencia IN ({$variantes['down']}) THEN '11'::integer "
            : '';

        $whereExcedencia = !empty($variantes['excedencia'])
            ? " WHEN dh05.nrovarlicencia IN ({$variantes['excedencia']}) THEN '10'::integer "
            : '';

        $whereVacaciones = !empty($variantes['vacaciones'])
            ? " WHEN dh05.nrovarlicencia IN ({$variantes['vacaciones']}) THEN '12'::integer "
            : '';

        $whereProtecIntegral = !empty($variantes['protecIntegral'])
            ? " WHEN dh05.nrovarlicencia IN ({$variantes['protecIntegral']}) THEN '51'::integer "
            : '';

        return [
            'sqlIlt' => $sqlIlt,
            'whereIlt' => $whereIlt,
            'whereDown' => $whereDown,
            'whereExcedencia' => $whereExcedencia,
            'whereVacaciones' => $whereVacaciones,
            'whereProtecIntegral' => $whereProtecIntegral
        ];
    }

    /**
     * Construye la condición para licencias no remuneradas.
     *
     * @param string $sqlIlt Condición SQL para licencias ILT
     * @return string Condición SQL completa
     */
    private function buildNoRemuneradasCondition(string $sqlIlt): string
    {
        return "(dl02.es_maternidad IS TRUE OR
                (NOT dl02.es_remunerada OR
                    (dl02.es_remunerada AND dl02.porcremuneracion = '0')
                )
                $sqlIlt
            )";
    }

    /**
     * Construye la consulta SQL completa para obtener licencias.
     *
     * @param string $whereLegajos Condición para filtrar por legajos
     * @param string $whereNorem Condición para licencias no remuneradas
     * @param string $whereDown Condición para licencias por maternidad down
     * @param string $whereExcedencia Condición para licencias por excedencia
     * @param string $whereIlt Condición para licencias ILT
     * @param string $whereVacaciones Condición para licencias por vacaciones
     * @param string $whereProtecIntegral Condición para licencias por protección integral
     * @return string Consulta SQL completa
     */
    private function buildFullQuery(
        string $whereLegajos,
        string $whereNorem,
        string $whereDown,
        string $whereExcedencia,
        string $whereIlt,
        string $whereVacaciones,
        string $whereProtecIntegral
    ): string {
        // Parte común de la consulta para ambas uniones
        $commonSelect = "
            CASE
                WHEN dh05.fec_desde <= ?::date THEN date_part('day', ?::timestamp)::integer
                ELSE date_part('day', dh05.fec_desde::timestamp)
            END AS inicio,
            CASE
                WHEN dh05.fec_hasta > ?::date OR dh05.fec_hasta IS NULL THEN date_part('day', ?::timestamp)::integer
                ELSE date_part('day', dh05.fec_hasta::timestamp)
            END AS final,
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
            dh05.fec_hasta";

        // Primera parte de la consulta (por legajo)
        $query1 = "SELECT
                dh01.nro_legaj,
                $commonSelect,
                TRUE AS es_legajo,
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
                AND $whereLegajos";

        // Segunda parte de la consulta (por cargo)
        $query2 = "SELECT
                dh01.nro_legaj,
                $commonSelect,
                FALSE AS es_legajo,
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

        // Unir ambas consultas
        return "$query1 UNION $query2";
    }

    /**
     * Obtiene los parámetros para la consulta SQL.
     *
     * @param string $fechaInicio Fecha de inicio del período
     * @param string $fechaFin Fecha de fin del período
     * @return array Parámetros para la consulta
     */
    private function getQueryParams(string $fechaInicio, string $fechaFin): array
    {
        return [
            $fechaInicio, $fechaInicio, $fechaFin, $fechaFin, $fechaFin, $fechaInicio,
            $fechaInicio, $fechaInicio, $fechaFin, $fechaFin, $fechaFin, $fechaInicio
        ];
    }

    /**
     * Obtiene los números de legajo que tienen licencias vigentes en el período actual.
     *
     * @return array Array con los números de legajo que tienen licencias vigentes
     */
    public function getLegajosConLicenciasVigentes(): array
    {
        try {
            // Obtener parámetros de fecha desde la configuración
            $fechaInicio = MapucheConfig::getFechaInicioPeriodoCorriente();
            $fechaFin = MapucheConfig::getFechaFinPeriodoCorriente();

            // Preparar condiciones para tipos de licencias
            $variantesConfig = $this->getVariantesLicencias();
            $condicionesLicencias = $this->buildLicenciasConditions($variantesConfig);
            $whereNorem = $this->buildNoRemuneradasCondition($condicionesLicencias['sqlIlt']);

            // Consulta SQL para obtener todos los legajos con licencias vigentes
            $sql = "
                SELECT DISTINCT dh01.nro_legaj
                FROM {$this->esquema}.dh05
                LEFT OUTER JOIN {$this->esquema}.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
                LEFT OUTER JOIN {$this->esquema}.dh01 ON (dh05.nro_legaj = dh01.nro_legaj)
                WHERE dh05.nro_legaj IS NOT NULL
                AND (fec_desde <= ?::date AND (fec_hasta is null OR fec_hasta >= ?::date))
                AND suc.map_es_licencia_vigente(dh05.nro_licencia)
                AND $whereNorem

                UNION

                SELECT DISTINCT dh01.nro_legaj
                FROM {$this->esquema}.dh05
                LEFT OUTER JOIN {$this->esquema}.dh03 ON (dh03.nro_cargo = dh05.nro_cargo)
                LEFT OUTER JOIN {$this->esquema}.dh01 ON (dh03.nro_legaj = dh01.nro_legaj)
                LEFT OUTER JOIN {$this->esquema}.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
                WHERE dh05.nro_cargo IS NOT NULL
                AND (fec_desde <= ?::date AND (fec_hasta is null OR fec_hasta >= ?::date))
                AND {$this->esquema}.map_es_cargo_activo(dh05.nro_cargo)
                AND suc.map_es_licencia_vigente(dh05.nro_licencia)
                AND $whereNorem
            ";

            // Ejecutar consulta
            $params = [$fechaInicio, $fechaInicio, $fechaInicio, $fechaInicio];
            $resultados = DB::connection($this->getConnectionName())->select($sql, $params);

            // Extraer y devolver solo los números de legajo
            return collect($resultados)->pluck('nro_legaj')->toArray();

        } catch (\Exception $e) {
            Log::error('Error al obtener legajos con licencias vigentes: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            // Retornar array vacío en caso de error
            return [];
        }
    }
}
