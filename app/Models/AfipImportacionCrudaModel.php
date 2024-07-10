<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AfipImportacionCrudaModel extends Model
{
    protected $connection = 'pgsql-mapuche';
    protected $table = 'suc.afip_importacion_cruda';
    public $timestamps = false;
    protected $fillable = ['linea_completa'];



    /**
     * Importa un archivo de datos de AFIP y los inserta en la tabla 'afip_importacion_cruda'.
     *
     * Este método se encarga de abrir el archivo especificado en la solicitud, leer cada línea del archivo
     * y almacenarla en la tabla 'afip_importacion_cruda'. Antes de importar los datos, se verifica que
     * la tabla esté vacía y, si no lo está, se trunca para asegurar que solo se inserten los datos del
     * archivo actual.
     *
     * @param Request $request Solicitud HTTP que contiene la ruta del archivo a importar.
     * @return int Número de líneas procesadas.
     * @throws \Exception Si no se puede abrir el archivo.
     */
    public function importarArchivo(string $filePath)
    {
        // Limpiar la tabla si no está vacía
        $this->truncateTableIfNotEmpty();

        // Abrir el archivo en modo lectura
        $archivo = fopen($filePath, "r");

        // Verificar que el archivo se abrió correctamente
        if (!$archivo) {
            throw new \Exception("No se pudo abrir el archivo");
        }

        // Desactivar temporalmente las restricciones de la base de datos
        DB::connection($this->connection)->statement('SET CONSTRAINTS ALL DEFERRED');

        // Iniciar una transacción
        DB::connection($this->connection)->beginTransaction();

        try {
            $batchSize = 1000; // Tamaño del lote
            $batchData = [];

            // Leer el archivo línea por línea
            while (($linea = fgets($archivo)) !== false) {
                $lineaUtf8 = mb_convert_encoding($linea, 'UTF-8', 'auto');
                $batchData[] = ['linea_completa' => $lineaUtf8];

                // Insertar en lotes
                if (count($batchData) >= $batchSize) {
                    DB::connection($this->connection)
                        ->table($this->table)
                        ->insert($batchData);
                    $batchData = [];
                }
            }

            // Insertar las líneas restantes
            if (!empty($batchData)) {
                DB::connection($this->connection)
                    ->table($this->table)
                    ->insert($batchData);
            }

            // Confirmar la transacción
            DB::connection($this->connection)->commit();
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::connection($this->connection)->rollBack();
            throw $e;
        } finally {
            // Cerrar el archivo
            fclose($archivo);

            // Restaurar las restricciones de la base de datos
            DB::connection($this->connection)->statement('SET CONSTRAINTS ALL IMMEDIATE');
        }

        // Retornar número de líneas procesadas
        return $this->count();
    }


    /**
    * Obtiene los datos importados de la tabla 'afip_importacion_cruda'.
    * Retorna los datos de la tabla afip_importacion_cruda.
    * @return \Illuminate\Support\Collection
    */
public function getDatosImportados()
    {
        return DB::connection($this->connection)
            ->table($this->table)
            ->get();

    }
    public function getQuery()
    {
        return $this->query();
    }

    private function truncateTableIfNotEmpty()
    {
        // Verificar si la tabla existe
        if (DB::connection($this->connection)->getSchemaBuilder()->hasTable($this->table)) {
            // Verificar si la tabla no esta vacia
            if (DB::connection($this->connection)->table($this->table)->count() > 0) {
                // Eliminar la tabla
                DB::connection($this->connection)->table($this->table)->truncate();
            }
        }
    }

}
