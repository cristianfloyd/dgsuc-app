<?php

namespace App\services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AfipRelacionesActivas;
use Illuminate\Support\LazyCollection;

class DatabaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
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
        } catch (\Exception $e) {
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
        $connection = DB::connection('pgsql-mapuche'); // Conexión a la base de datos específica

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
        } catch (\Exception $e) {
            $connection->rollBack(); // Revierte la transacción en caso de error
            Log::error('Error al insertar datos masivos: ' . $e->getMessage());
            return false;
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
    public function mapearDatosAlModelo(array $linea)
    {
        return AfipRelacionesActivas::mapearDatosAlModelo($linea);
    }
}
