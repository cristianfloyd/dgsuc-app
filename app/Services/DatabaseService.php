<?php

namespace app\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AfipRelacionesActivas;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\LazyCollection;
use App\Contracts\DatabaseServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class DatabaseService implements DatabaseServiceInterface
{
    use MapucheConnectionTrait;
    private static $connectionInstance = null;
    private const int DEFAULT_CHUNK_SIZE = 1000;

    
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    protected static function getMapucheConnection()
    {
        if (self::$connectionInstance === null) {
            $model = new static;
            self::$connectionInstance = $model->getConnectionFromTrait();
        }
        return self::$connectionInstance;
    }

    /** Inserta datos de manera masiva en la base de datos.
     *
     * @param array $datosMapeados Un arreglo de datos que se insertarán en la base de datos.
     * @return bool Retorna true si la inserción fue exitosa, de lo contrario, false.
     */
    public function insertarDatosMasivos(array $datosMapeados): bool
    {
        $tamanoLote = 1000; // Ajusta este valor según tus necesidades

        try {
            // Inicia una transacción de base de datos
            DB::beginTransaction();

            // Crea una colección perezosa a partir de los datos mapeados
            $resultado = LazyCollection::make($datosMapeados)
                // Divide la colección en lotes del tamaño especificado
                ->chunk($tamanoLote)
                // Inserta cada lote en la base de datos
                ->each(function ($lote) {
                    AfipRelacionesActivas::insert($lote->toArray());
                });

            // Confirma la transacción
            DB::commit();

            // Registra un mensaje de éxito en el log
            Log::info('Se importaron los datos correctamente');
            return true;
        } catch (Exception $e) {
            // Revierte la transacción en caso de error
            DB::rollBack();
            // Registra un mensaje de error en el log
            Log::error('Error al insertar datos masivos: ' . $e->getMessage());
            return false;
        }
    }

    /** Inserta datos de manera masiva en la base de datos utilizando una conexión específica.
     *
     * @param array $datosMapeados Los datos que se desean insertar.
     * @return bool Retorna true si la inserción fue exitosa, false en caso contrario.
     */
    public function insertarDatosMasivos2(array $datosMapeados): bool
    {
        $tamanoLote = 1000; // Ajusta este valor según tus necesidades
        $connection = static::getConnectionFromTrait(); // Conexión a la base de datos específica

        try {
            $connection->beginTransaction(); // Inicia la transacción

            // Divide los datos en lotes y los inserta
            foreach (array_chunk($datosMapeados, $tamanoLote) as $lote) {
                // Crea los placeholders para la consulta
                $placeholders = implode(',', array_fill(0, count($lote[0]), '?'));

                // Construye la consulta de inserción
                $query = "INSERT INTO suc.afip_relaciones_activas (" . implode(',', array_keys($lote[0])) . ") VALUES ($placeholders)";

                // Prepara la consulta
                $statement = $connection->getPdo()->prepare($query);

                // Ejecuta la consulta para cada fila en el lote
                foreach ($lote as $row) {
                    $statement->execute(array_values($row));
                }
            }

            $connection->commit(); // Confirma la transacción

            Log::info('Se importaron los datos correctamente');
            return true;
        } catch (Exception $e) {
            $connection->rollBack(); // Revierte la transacción en caso de error
            Log::error('Error al insertar datos masivos: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Inserta datos de manera masiva en la base de datos utilizando una conexión específica.
     *
     * Este método recibe una colección de datos mapeados y los inserta en lotes en la base de datos
     * utilizando una conexión específica. Si no se encuentran datos para insertar, se devuelve un
     * mensaje de error. En caso de éxito, se registra un mensaje informativo en el log y se devuelve
     * un array con información sobre la inserción. En caso de error, se revierte la transacción y se
     * registra un mensaje de error en el log.
     *
     * @param Collection $mappedData La colección de datos mapeados que se desean insertar.
     * @param string $tableName El nombre de la tabla donde se insertarán los datos.
     * @param int $chunkSize El tamaño de los lotes en los que se dividirán los datos. Por defecto es 1000.
     * @return array Un array con información sobre el resultado de la inserción.
     */
    public function insertBulkData(Collection $mappedData, string $tableName, int $chunkSize = self::DEFAULT_CHUNK_SIZE): array
    {
        $conexion = $this->getConnectionName();
        Log::info("Iniciando inserción en la tabla: $tableName y la conexion $conexion");

        if ($mappedData->isEmpty()) {
            return [
                'success' => false,
                'message' => "No se encontraron datos para insertar en la tabla: $tableName",
                'data' => ['tableName' => $tableName, 'connection' => $conexion]
            ];
        }

        $rowInserted = 0;

        try {
            DB::connection($conexion)->beginTransaction();

            $mappedData->chunk($chunkSize)->each(function ($chunk) use ($conexion, $tableName, &$rowInserted) {
                $processedChunk = $chunk->map(function ($data) {
                    return collect($data)->except('id')->toArray();
                });

                $inserted = DB::connection($conexion)->table($tableName)->insert($processedChunk->toArray());
                $rowInserted += $inserted;
            });

            DB::connection($conexion)->commit();

            Log::info("Se insertaron $rowInserted filas en la tabla: $tableName");
            return [
                'success' => true,
                'message' => "Inserción completada con éxito",
                'data' => [
                    'tableName' => $tableName,
                    'connection' => $conexion,
                    'rowsInserted' => $rowInserted
                ]
            ];
        } catch (Exception $e) {
            DB::connection($conexion)->rollBack();
            Log::error('Error al insertar datos en ' . $tableName . ': ' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error al insertar datos en $tableName",
                'data' => [
                    'tableName' => $tableName,
                    'connection' => $conexion,
                    'error' => $e->getMessage()
                ]
            ];
        }
    }


    /** Mapea los datos de una línea al modelo AfipRelacionesActivas.
     *
     * Este método toma un array de datos y lo transforma en un array
     * que sigue la estructura del modelo AfipRelacionesActivas.
     *
     * @param array $linea Los datos que se desean mapear.
     * @return array Los datos mapeados al modelo.
     */
    public function mapearDatosAlModelo(array $linea): array
    {
        return AfipRelacionesActivas::mapearDatosAlModelo($linea);
    }

    private function sanitizeData($data)
    {
        return array_map(function ($item) {
            return preg_replace('/[\x00-\x1F\x7F-\xFF]/', '?', $item);
        }, $data);
    }
}
