<?php

namespace App\Services\Mapuche;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Mapuche\MapucheConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
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
     * Tiempo de expiración del caché en segundos (por defecto 1 hora)
     *
     * @var int
     */
    protected int $cacheExpirationTime;

    /**
     * Prefijo para las claves de caché
     *
     * @var string
     */
    protected string $cachePrefix;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->esquema = 'mapuche';
        // Tiempo de caché configurable (1 hora por defecto)
        $this->cacheExpirationTime = config('cache.licencias_expiration', 3600);
        // Prefijo para las claves de caché
        $this->cachePrefix = config('cache.prefix', 'laravel_') . 'licencias:';
    }

    /**
     * Obtiene las licencias vigentes para los legajos indicados.
     * Implementa caché Redis para mejorar el rendimiento.
     *
     * @param array $legajos Array de números de legajo
     * @param bool $useCache Indica si se debe usar caché o forzar consulta fresca
     * @return DataCollection
     */
    public function getLicenciasVigentes(array $legajos, bool $useCache = true): DataCollection
    {
        try {
            // Validar que existan legajos para consultar
            if (empty($legajos)) {
                Log::warning('Se intentó consultar licencias sin proporcionar legajos');
                return new DataCollection(LicenciaVigenteData::class, []);
            }

            // Ordenar legajos para consistencia en la clave de caché
            sort($legajos);

            // Generar clave de caché única basada en los legajos y el período actual
            $periodoActual = MapucheConfig::getPeriodoFiscal();
            $cacheKey = $this->cachePrefix . "vigentes:{$periodoActual}:" . md5(json_encode($legajos));

            // Si no se debe usar caché o hay demasiados legajos, ejecutar consulta directamente
            if (!$useCache || count($legajos) > 100) {
                return $this->fetchLicenciasFromDatabase($legajos);
            }

            // Verificar si los datos están en caché
            if (Redis::exists($cacheKey)) {
                Log::info('Obteniendo licencias desde caché Redis', ['legajos_count' => count($legajos)]);
                $cachedData = json_decode(Redis::get($cacheKey), true);

                // Si los datos cacheados están vacíos, verificar directamente en la base de datos para confirmar
                if (empty($cachedData)) {
                    Log::warning('Caché Redis devolvió datos vacíos, verificando directamente en base de datos', [
                        'legajos' => $legajos
                    ]);
                    $licenciasFrescas = $this->fetchLicenciasFromDatabase($legajos);

                    // Si encontramos datos en la BD pero no en caché, actualizamos la caché
                    if ($licenciasFrescas->count() > 0) {
                        $licenciasArray = $licenciasFrescas->toArray();
                        Redis::setex($cacheKey, $this->cacheExpirationTime, json_encode($licenciasArray));
                        Log::info('Caché actualizado con datos encontrados en la base de datos', [
                            'legajos' => $legajos,
                            'count' => $licenciasFrescas->count()
                        ]);
                        return $licenciasFrescas;
                    }
                }

                return new DataCollection(LicenciaVigenteData::class, $cachedData);
            }

            // Si no está en caché, obtener de la base de datos
            Log::info('Cargando licencias desde la base de datos para caché', ['legajos_count' => count($legajos)]);
            $licencias = $this->fetchLicenciasFromDatabase($legajos);

            // Guardar en caché si hay resultados
            if ($licencias->count() > 0) {
                // Convertir a array para almacenar en Redis
                $licenciasArray = $licencias->toArray();
                Redis::setex($cacheKey, $this->cacheExpirationTime, json_encode($licenciasArray));
                Log::info('Licencias encontradas y guardadas en caché', [
                    'legajos' => $legajos,
                    'count' => $licencias->count()
                ]);
            } else {
                Log::warning('No se encontraron licencias en la base de datos para los legajos consultados', [
                    'legajos' => $legajos
                ]);
            }

            return $licencias;
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
     * Implementa caché individual por legajo con Redis.
     *
     * @param int $legajo Número de legajo
     * @param bool $useCache Indica si se debe usar caché o forzar consulta fresca
     * @return DataCollection
     */
    public function getLicenciasVigentesPorLegajo(int $legajo, bool $useCache = true): DataCollection
    {
        $periodoActual = MapucheConfig::getPeriodoFiscal();
        $cacheKey = $this->cachePrefix . "vigentes:legajo:{$periodoActual}:{$legajo}";

        if (!$useCache) {
            return $this->getLicenciasVigentes([$legajo], false);
        }

        // Verificar si los datos están en caché
        if (Redis::exists($cacheKey)) {
            Log::info('Obteniendo licencias para legajo individual desde caché Redis', ['legajo' => $legajo]);
            $cachedData = json_decode(Redis::get($cacheKey), true);
            return new DataCollection(LicenciaVigenteData::class, $cachedData);
        }

        // Si no está en caché, obtener de la base de datos
        Log::info('Cargando licencias para legajo individual desde la base de datos', ['legajo' => $legajo]);
        $licencias = $this->getLicenciasVigentes([$legajo], false);

        // Guardar en caché si hay resultados
        if ($licencias->count() > 0) {
            $licenciasArray = $licencias->toArray();
            Redis::setex($cacheKey, $this->cacheExpirationTime, json_encode($licenciasArray));
        }

        return $licencias;
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
            $fechaInicio,
            $fechaInicio,
            $fechaFin,
            $fechaFin,
            $fechaFin,
            $fechaInicio,
            $fechaInicio,
            $fechaInicio,
            $fechaFin,
            $fechaFin,
            $fechaFin,
            $fechaInicio
        ];
    }

    /**
     * Ejecuta la consulta a la base de datos para obtener licencias.
     * Método interno utilizado por el sistema de caché.
     *
     * @param array $legajos Array de números de legajo
     * @return DataCollection
     */
    private function fetchLicenciasFromDatabase(array $legajos): DataCollection
    {
        // Obtener parámetros de fecha desde la configuración
        $fechaInicio = MapucheConfig::getFechaInicioPeriodoCorriente();
        $fechaFin = MapucheConfig::getFechaFinPeriodoCorriente();

        // Construir la consulta SQL
        $sql = $this->buildLicenciasQuery($legajos);

        // Parámetros para la consulta
        $params = $this->getQueryParams($fechaInicio, $fechaFin);

        // Registrar la consulta para depuración
        Log::debug('Consulta SQL de licencias vigentes', [
            'sql' => $sql,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'legajos' => $legajos
        ]);

        try {
            // Ejecutar la consulta
            $resultados = DB::connection($this->getConnectionName())
                ->select($sql, $params);

            Log::info('Consulta SQL ejecutada correctamente', [
                'filas_encontradas' => count($resultados)
            ]);

            // Convertir resultados a DTO
            return LicenciaVigenteData::fromResultados($resultados);
        } catch (\Exception $e) {
            Log::error('Error al ejecutar consulta SQL de licencias', [
                'error' => $e->getMessage(),
                'legajos' => $legajos
            ]);

            throw $e;
        }
    }

    /**
     * Obtiene los números de legajo que tienen licencias vigentes en el período actual.
     * Implementa caché Redis para esta consulta frecuente.
     *
     * @param bool $useCache Indica si se debe usar caché o forzar consulta fresca
     * @return array Array con los números de legajo que tienen licencias vigentes
     */
    public function getLegajosConLicenciasVigentes(bool $useCache = true): array
    {
        try {
            $periodoActual = MapucheConfig::getPeriodoFiscal();
            $cacheKey = $this->cachePrefix . "legajos:{$periodoActual}";

            if (!$useCache) {
                return $this->fetchLegajosConLicenciasFromDatabase();
            }

            // Verificar si los datos están en caché
            if (Redis::exists($cacheKey)) {
                Log::info('Obteniendo legajos con licencias desde caché Redis');
                return json_decode(Redis::get($cacheKey), true);
            }

            // Si no está en caché, obtener de la base de datos
            Log::info('Cargando legajos con licencias desde la base de datos para caché');
            $legajos = $this->fetchLegajosConLicenciasFromDatabase();

            // Guardar en caché
            if (!empty($legajos)) {
                Redis::setex($cacheKey, $this->cacheExpirationTime, json_encode($legajos));
            }

            return $legajos;
        } catch (\Exception $e) {
            Log::error('Error al obtener legajos con licencias vigentes: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            // Retornar array vacío en caso de error
            return [];
        }
    }

    /**
     * Ejecuta la consulta a la base de datos para obtener legajos con licencias.
     * Método interno utilizado por el sistema de caché.
     *
     * @return array
     */
    private function fetchLegajosConLicenciasFromDatabase(): array
    {
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
    }

    /**
     * Invalida el caché de licencias para los legajos especificados.
     * Utiliza patrones de Redis para una invalidación más eficiente.
     *
     * @param array|null $legajos Legajos específicos o null para invalidar todo
     * @return void
     */
    public function invalidateCache(?array $legajos = null): void
    {
        $periodoActual = MapucheConfig::getPeriodoFiscal();

        if ($legajos === null) {
            // Invalidar todo el caché de licencias usando patrones de Redis
            $pattern = $this->cachePrefix . "*:{$periodoActual}:*";
            $this->deleteKeysByPattern($pattern);

            // También invalidar la lista de legajos con licencias
            Redis::del($this->cachePrefix . "legajos:{$periodoActual}");

            Log::info('Caché global de licencias invalidado');
        } else {
            // Invalidar caché para legajos específicos
            foreach ($legajos as $legajo) {
                Redis::del($this->cachePrefix . "vigentes:legajo:{$periodoActual}:{$legajo}");
            }

            // Si hay varios legajos, también invalidar su caché conjunto
            if (count($legajos) > 1) {
                sort($legajos);
                $cacheKey = $this->cachePrefix . "vigentes:{$periodoActual}:" . md5(json_encode($legajos));
                Redis::del($cacheKey);
            }

            Log::info('Caché de licencias invalidado para legajos específicos', ['legajos' => $legajos]);
        }
    }

    /**
     * Elimina claves de Redis que coinciden con un patrón.
     * Útil para invalidación de caché por patrones.
     *
     * @param string $pattern Patrón de claves a eliminar
     * @return void
     */
    private function deleteKeysByPattern(string $pattern): void
    {
        $keys = Redis::keys($pattern);
        if (!empty($keys)) {
            foreach ($keys as $key) {
                // Extraer el nombre real de la clave (Redis::keys devuelve claves con prefijo)
                $realKey = str_replace(Redis::connection()->getPrefix(), '', $key);
                Redis::del($realKey);
            }
        }
    }

    /**
     * Verifica si un legajo tiene una licencia de maternidad activa.
     *
     * Esta función determina si un legajo específico tiene una licencia de maternidad activa dentro del período fiscal actual.
     * Para esto, calcula el rango de fechas del período fiscal actual y consulta las licencias de maternidad asociadas al legajo,
     * tanto en el contexto del legajo como del cargo. Si se encuentra una licencia activa, devuelve true; de lo contrario, devuelve false.
     *
     * @param int $nro_legajo El número de legajo a verificar.
     * @return bool Verdadero si el legajo tiene una licencia de maternidad activa, falso de lo contrario.
     */
    public static function tieneLicenciaMaternidadActiva($nro_legajo)
    {
        //-- Armo la fecha inicial para la consulta.
        $fecha_inicio = MapucheConfig::getFechaInicioPeriodoCorriente();
        //-- Armo la fecha tope para la consulta.
        $mes_final = MapucheConfig::getMesFiscal() + 1;
        $anio_final = MapucheConfig::getAnioFiscal();
        if ($mes_final > 12) {
            $mes_final = 1;
            $anio_final++;
        }
        $fecha_final = $anio_final . "-" . $mes_final . "-01";
        if (self::tieneLicenciaMaternidadEnLegajo($nro_legajo, $fecha_inicio, $fecha_final)) {
            return true;
        }
        if (self::tieneLicenciaMaternidadEnCargo($nro_legajo, $fecha_inicio, $fecha_final)) {
            return true;
        }
        return false;
    }

    private static function tieneLicenciaMaternidadEnLegajo($nro_legajo, $fecha_inicio, $fecha_final)
    {

        $sql = "SELECT
				COUNT(*) as cantidad
			FROM
				mapuche.dh05 dh05,
				mapuche.dl02 dl02
			WHERE
					dh05.NroVarLicencia = dl02.NroVarLicencia
				AND	dL02.Es_Maternidad <> FALSE
				AND	dh05.nro_legaj = $nro_legajo
                                AND     (dh05.fec_desde, COALESCE(dh05.fec_hasta,'31-12-2999'::date))
                                OVERLAPS ($fecha_inicio::date, $fecha_final::date);
			";

        $rs = DB::connection(self::getStaticConnectionName())->select($sql);

        return $rs['cantidad'] > 0;
    }

    private static function tieneLicenciaMaternidadEnCargo($nro_legajo, $fecha_inicio, $fecha_final)
    {


        $sql = "SELECT
				COUNT(*) as cantidad
			FROM
				mapuche.dh05 dh05,
				mapuche.dl02 dl02,
				mapuche.dh03 dh03
			WHERE
					dh05.NroVarLicencia = dl02.NroVarLicencia
				AND	dh05.nro_cargo = dh03.nro_cargo
				AND 	dh03.nro_legaj = $nro_legajo
				AND	dL02.Es_Maternidad <> FALSE
				AND	dh05.nro_legaj is NULL
                                AND     (dh05.fec_desde, COALESCE(dh05.fec_hasta,'31-12-2999'::date))
                                OVERLAPS ($fecha_inicio::date, $fecha_final::date);
			";

        $rs = DB::connection(self::getStaticConnectionName())->select($sql);

        return $rs['cantidad'] > 0;
    }

    private static function getStaticConnectionName()
    {
        $instance = new static();
        return $instance->getConnectionName();
    }

    /**
     * Obtiene los legajos con licencias sin goce
     *
     * @param string $whereLegajo Condición adicional para filtrar legajos
     * @return array Array con los números de legajo que tienen licencias sin goce
     */
    public static function getLegajosLicenciasSinGoce(string $whereLegajo): array
    {
        $subquery1 = self::getSubqueryLicenciasPorLegajo($whereLegajo);
        $subquery2 = self::getSubqueryLicenciasPorCargo($whereLegajo);

        $sql = "SELECT unnest(array({$subquery1} UNION {$subquery2})) AS nro_legaj";

        $resultados = DB::connection(self::getStaticConnectionName())->select($sql);
        return collect($resultados)->pluck('nro_legaj')->toArray();
    }

    /**
     * Obtiene la subconsulta para licencias por legajo
     *
     * @param string $whereLegajo
     * @return string
     */
    private static function getSubqueryLicenciasPorLegajo(string $whereLegajo): string
    {
        return "
        SELECT dh01.nro_legaj
        FROM mapuche.dh05
        LEFT JOIN mapuche.dl02 ON dh05.nrovarlicencia = dl02.nrovarlicencia
        LEFT JOIN mapuche.dh01 ON dh05.nro_legaj = dh01.nro_legaj
        WHERE dh05.nro_legaj IS NOT NULL
        AND suc.map_es_licencia_vigente(dh05.nro_licencia)
        AND (NOT dl02.es_remunerada OR (dl02.es_remunerada AND dl02.porcremuneracion = 0))
        AND {$whereLegajo}";
    }

    /**
     * Obtiene la subconsulta para licencias por cargo
     *
     * @param string $whereLegajo
     * @return string
     */
    private static function getSubqueryLicenciasPorCargo(string $whereLegajo): string
    {
        return "
        SELECT DISTINCT dh01.nro_legaj
        FROM mapuche.dh05
        LEFT JOIN mapuche.dh03 ON dh03.nro_cargo = dh05.nro_cargo
        LEFT JOIN mapuche.dh01 ON dh03.nro_legaj = dh01.nro_legaj
        LEFT JOIN mapuche.dl02 ON dh05.nrovarlicencia = dl02.nrovarlicencia
        WHERE dh05.nro_cargo IS NOT NULL
        AND mapuche.map_es_cargo_activo(dh05.nro_cargo)
        AND suc.map_es_licencia_vigente(dh05.nro_licencia)
        AND (NOT dl02.es_remunerada OR (dl02.es_remunerada AND dl02.porcremuneracion = 0))
        AND {$whereLegajo}";
    }

    /**
     * Método original para obtener legajos con licencias sin goce
     *
     * @param string $whereLegajo Condición adicional para filtrar legajos
     * @return array
     */
    public static function getLegajosLicenciasSinGoceOriginal(string $whereLegajo): array
    {
        $sql = "
        SELECT array(
            SELECT dh01.nro_legaj
            FROM mapuche.dh05
            LEFT JOIN mapuche.dl02 ON dh05.nrovarlicencia = dl02.nrovarlicencia
            LEFT JOIN mapuche.dh01 ON dh05.nro_legaj = dh01.nro_legaj
            WHERE dh05.nro_legaj IS NOT NULL
            AND suc.map_es_licencia_vigente(dh05.nro_licencia)
            AND (NOT dl02.es_remunerada OR (dl02.es_remunerada AND dl02.porcremuneracion = 0))
            AND {$whereLegajo}

            UNION

            SELECT DISTINCT dh01.nro_legaj
            FROM mapuche.dh05
            LEFT JOIN mapuche.dh03 ON dh03.nro_cargo = dh05.nro_cargo
            LEFT JOIN mapuche.dh01 ON dh03.nro_legaj = dh01.nro_legaj
            LEFT JOIN mapuche.dl02 ON dh05.nrovarlicencia = dl02.nrovarlicencia
            WHERE dh05.nro_cargo IS NOT NULL
            AND mapuche.map_es_cargo_activo(dh05.nro_cargo)
            AND suc.map_es_licencia_vigente(dh05.nro_licencia)
            AND (NOT dl02.es_remunerada OR (dl02.es_remunerada AND dl02.porcremuneracion = 0))
            AND {$whereLegajo}
        ) AS licencias_sin_goce";

        return DB::connection(self::getStaticConnectionName())->select($sql);
    }
}
