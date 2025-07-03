<?php

namespace App\Models;

use Sushi\Sushi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


/**
 * @method static hydrate(array $results)
 * @method static collection(array $array)
 */
class EmbargoProcesoResult extends Model
{
    use MapucheConnectionTrait;
    protected $table = 'suc.embargo_proceso_results';

    protected $primaryKey = 'id';
    public $incrementing = true;

    // Deshabilitar timestamps ya que no son parte del resultado de la consulta
    public $timestamps = false;



    // Definir los atributos rellenables basados en las columnas del resultado de la consulta
    protected $fillable = [
        'nro_liqui',
        'nros_liqui_json',
        'tipo_noved',
        'vig_noano',
        'vig_nomes',
        'tipo_embargo',
        'nro_legaj',
        'remunerativo',
        'no_remunerativo',
        'total',
        'codn_conce',
        'tipo_foran',
        'clas_noved'
    ];

    // Definir la conversión de atributos para los tipos de datos apropiados
    protected $casts = [
        'nro_liqui' => 'integer',
        'nros_liqui_json' => 'json',
        'tipo_noved' => 'string',
        'vig_noano' => 'integer',
        'vig_nomes' => 'integer',
        'tipo_embargo' => 'integer',
        'nro_legaj' => 'integer',
        'remunerativo' => 'decimal:2',
        'no_remunerativo' => 'decimal:2',
        'total' => 'decimal:2',
        'codn_conce' => 'integer',
        'tipo_foran' => 'string',
        'clas_noved' => 'string'
    ];



    /**
     * Obtiene una consulta vacía
     *
     * @return Builder
     */
    public function getEmptyQuery(): Builder
    {
        return self::query()->whereRaw('1=0');
    }

    /**
     * Obtiene el monto total
     *
     * @return float
     */
    public function getTotalAmount(): float
    {
        return $this->remunerativo + $this->no_remunerativo;
    }

    /**
     * Verifica si es embargo bruto
     *
     * @return bool
     */
    public function isBrutoEmbargo(): bool
    {
        return $this->tipo_embargo === 265;
    }

    /**
     * Ejecuta el proceso de embargo usando la función almacenada.
     *
     * @param array $complementarias Liquidaciones complementarias
     * @param int $liquidacionDefinitiva Número de liquidación definitiva
     * @param int $liquidacionProxima Número de liquidación próxima
     * @param bool $insertIntoDh25 Indica si se debe insertar en la tabla DH25
     * @param int $periodoFiscal Periodo fiscal en formato AAAAMM
     * @return array Resultados del proceso de embargo
     * @throws \Exception Si ocurre un error durante el proceso
     */
    public static function ejecutarProcesoEmbargo(
        array $complementarias,
        int $liquidacionDefinitiva,
        int $liquidacionProxima,
        bool $insertIntoDh25,
        int $periodoFiscal
    ): array {
        try {
            // Obtener la conexión correcta para este modelo
            $connection = self::obtenerConexion();
            $connectionName = $connection->getName();

            Log::debug('Conexion obtenida:', ['connection' => $connectionName]);

            // Verificar si el esquema existe USANDO LA MISMA CONEXIÓN
            $schemaExists = $connection->select(
                "SELECT schema_name FROM information_schema.schemata WHERE schema_name = 'suc'"
            );

            if (empty($schemaExists)) {
                // Verificar qué esquemas están disponibles para diagnóstico
                $availableSchemas = $connection->select(
                    "SELECT schema_name FROM information_schema.schemata ORDER BY schema_name"
                );

                Log::error('Esquema "suc" no encontrado. Esquemas disponibles:', [
                    'connection' => $connectionName,
                    'schemas' => array_column($availableSchemas, 'schema_name')
                ]);

                throw new \Exception("El esquema 'suc' no existe en la base de datos '{$connectionName}'.");
            }

            // Convertir el array de complementarias en formato PostgreSQL
            $complementariasArray = !empty($complementarias)
                ? '{' . implode(',', $complementarias) . '}'
                : '{0}';

            // Verificar si la función existe
            $functionExists = $connection->select(
                "SELECT routine_name FROM information_schema.routines
                 WHERE routine_schema = 'suc' AND routine_name = 'emb_proceso'"
            );

            if (empty($functionExists)) {
                throw new \Exception("La función 'suc.emb_proceso' no existe en la base de datos.");
            }

            
            // Ejecutar la función almacenada con la conexión obtenida
            $results = $connection->select(
                "SELECT * FROM suc.emb_proceso(
                    ?::integer[],
                    ?::integer,
                    ?::integer,
                    ?::boolean,
                    ?::integer
                )",
                [
                    $complementariasArray,
                    $liquidacionDefinitiva,
                    $liquidacionProxima,
                    $insertIntoDh25,
                    $periodoFiscal
                ]
            );

            // Guardar los resultados en la tabla
            $registrosInsertados = self::guardarResultadosProceso($results);

            Log::info('Proceso de embargo ejecutado y resultados guardados correctamente', [
                'resultados_obtenidos' => count($results),
                // 'resultados_insertados' => $registrosInsertados,
                'parametros' => [
                    'complementarias' => $complementarias,
                    'liquidacionDefinitiva' => $liquidacionDefinitiva,
                    'liquidacionProxima' => $liquidacionProxima,
                    'insertIntoDh25' => $insertIntoDh25,
                    'periodoFiscal' => $periodoFiscal
                ]
            ]);

            return $results;
        } catch (\Exception $e) {
            Log::error('Error al ejecutar proceso de embargo', [
                'mensaje' => $e->getMessage(),
                'parametros' => [
                    'complementarias' => $complementarias,
                    'liquidacionDefinitiva' => $liquidacionDefinitiva,
                    'liquidacionProxima' => $liquidacionProxima,
                    'insertIntoDh25' => $insertIntoDh25,
                    'periodoFiscal' => $periodoFiscal
                ]
            ]);

            throw $e;
        }
    }


    /**
    * Obtiene la conexión a la base de datos utilizada por este modelo.
    *
    * Este método estático proporciona acceso a la conexión configurada
    * en el trait MapucheConnectionTrait, haciéndola accesible para métodos
    * estáticos que necesitan ejecutar consultas directas a la base de datos.
    *
    * @return \Illuminate\Database\Connection
    * @throws \Exception Si no se puede determinar la conexión
    */
    protected static function obtenerConexion()
    {
        // Crear una instancia temporal para acceder a los métodos de instancia
        $instance = new static();

        // Verificar si el trait implementa algún método específico para obtener la conexión
        if (method_exists($instance, 'getMapucheConnection')) {
            return $instance->getMapucheConnection();
        }

        // Si no hay método específico, usar el connectionName definido en el trait o el modelo
        $connectionName = $instance->getConnectionName();

        if (empty($connectionName)) {
            // Si aún no hay nombre de conexión, intentar con la configuración predeterminada
            $connectionName = config('database.default');

            // Registrar advertencia
            Log::warning('Se está utilizando la conexión predeterminada porque no se pudo determinar la conexión específica', [
                'model' => get_class($instance),
                'defaultConnection' => $connectionName
            ]);
        }

        // Registrar información sobre la conexión utilizada
        Log::info('Obteniendo conexión para ejecución de función almacenada', [
            'connectionName' => $connectionName
        ]);

        return DB::connection($connectionName);
    }

    /**
     * Guarda los resultados del proceso de embargo en la tabla.
     *
     * @param array $results Resultados obtenidos de la función almacenada
     * @param bool $limpiarTabla Si se debe limpiar la tabla antes de insertar nuevos registros
     * @return int Número de registros insertados
     * @throws \Exception Si ocurre un error durante el proceso de inserción
     */
    public static function guardarResultadosProceso(array $results, bool $limpiarTabla = true): int
    {
        // Verificar si hay resultados para procesar
        $cantidadRegistros = count($results);
        if ($cantidadRegistros === 0) {
            Log::warning('No hay registros para insertar en la tabla de resultados de embargo');
            return 0;
        }

        // Obtener la conexión
        $connection = self::obtenerConexion();

        // Crear una instancia temporal para acceder a métodos no estáticos
        $instance = new static();
        $tableName = $instance->getTable();

        // Iniciar una transacción para garantizar la integridad de los datos
        $connection->beginTransaction();

        try {
            // Si se solicita limpiar la tabla, eliminar registros existentes
            if ($limpiarTabla) {
                // Usar la conexión obtenida para el truncate
                $connection->table($tableName)->truncate();
                Log::info('Tabla de resultados de embargo limpiada antes de la inserción');
            }

            // Preparar los datos para inserción, validando cada campo
            $registrosParaInsertar = [];
            $fillable = $instance->getFillable(); // Usar la instancia para obtener fillable
            $fillableSet = array_flip($fillable); // Conjunto de campos permitidos para búsqueda rápida

            foreach ($results as $index => $result) {
                // Convertir a array
                $registro = (array) $result;

                // Filtrar solo los campos válidos según $fillable
                $registroFiltrado = array_intersect_key($registro, $fillableSet);

                // --- NUEVO: poblar nros_liqui_json ---
                // Si ya viene un array de liquis, úsalo; si no, crea uno con el nro_liqui simple
                if (isset($registro['nros_liqui_json'])) {
                    // Si ya viene, úsalo tal cual
                    $registroFiltrado['nros_liqui_json'] = is_array($registro['nros_liqui_json'])
                        ? $registro['nros_liqui_json']
                        : json_decode($registro['nros_liqui_json'], true);
                } elseif (isset($registro['nro_liqui'])) {
                    // Si solo hay uno, lo guardamos como array de uno
                    $registroFiltrado['nros_liqui_json'] = [$registro['nro_liqui']];
                } else {
                    // Si no hay ninguno, lo dejamos como array vacío
                    $registroFiltrado['nros_liqui_json'] = [];
                }
                // --- FIN NUEVO ---

                
                
                // Verificar que tengamos todos los campos requeridos
                if (count($registroFiltrado) < count($fillable)) {
                    Log::warning("El registro {$index} no contiene todos los campos esperados", [
                        'esperados' => $fillable,
                        'recibidos' => array_keys($registroFiltrado)
                    ]);
                }
                // Convertir a JSON antes de insertar
                if (isset($registroFiltrado['nros_liqui_json']) && is_array($registroFiltrado['nros_liqui_json'])) {
                    $registroFiltrado['nros_liqui_json'] = json_encode($registroFiltrado['nros_liqui_json']);
                }
                
                $registrosParaInsertar[] = $registroFiltrado;
            }

            // Definir tamaño de bloque basado en la cantidad de registros
            // Para pocos registros, bloques más pequeños; para muchos, bloques más grandes
            $tamañoBloque = min(max(intval($cantidadRegistros / 10), 50), 1000);

            // Usar un contador para seguir los registros insertados correctamente
            $registrosInsertados = 0;

            // Insertar en bloques para optimizar rendimiento
            for ($i = 0; $i < $cantidadRegistros; $i += $tamañoBloque) {
                $bloque = array_slice($registrosParaInsertar, $i, $tamañoBloque);

                // Insertar usando la conexión obtenida, más eficiente que self::insert
                $connection->table($tableName)->insert($bloque);
                $registrosInsertados += count($bloque);

                // Log cada X bloques para no sobrecargar los logs
                if ($i % ($tamañoBloque * 10) === 0 || $i + $tamañoBloque >= $cantidadRegistros) {
                    Log::debug("Progreso de inserción: {$registrosInsertados}/{$cantidadRegistros} registros");
                }
            }

            // Confirmar la transacción
            $connection->commit();

            // Registro detallado del resultado
            Log::info("Proceso de embargo completado con éxito", [
                'total_registros' => $cantidadRegistros,
                'registros_insertados' => $registrosInsertados,
                'tiempo_proceso' => number_format(microtime(true) - LARAVEL_START, 4) . 's'
            ]);

            return $registrosInsertados;
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            $connection->rollBack();

            Log::error('Error al guardar resultados del proceso de embargo', [
                'mensaje' => $e->getMessage(),
                'codigo' => $e->getCode(),
                'total_resultados' => $cantidadRegistros,
                'trace' => $e->getTraceAsString()
            ]);

            throw new \Exception("Error al guardar los resultados del proceso de embargo: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    // #########################################################
    // ############## accesors y mutators #####################
    // #########################################################

    public function setNrosLiquiJsonAttribute($value)
    {
        $this->attributes['nros_liqui_json'] = json_encode($value);
    }

    public function getNrosLiquiJsonAttribute($value)
    {
        return json_decode($value, true);
    }
}
