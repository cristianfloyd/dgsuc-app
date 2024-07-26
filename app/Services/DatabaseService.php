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

    public function insertarDatosMasivos(array $datosMapeados)
    {
        $tamanoLote = 1000; // Ajusta este valor según tus necesidades

        try {
            DB::beginTransaction();

            $resultado = LazyCollection::make($datosMapeados)
                ->chunk($tamanoLote)
                ->each(function ($lote) {
                    AfipRelacionesActivas::insert($lote->toArray());
                });

            DB::commit();

            Log::info('Se importaron los datos correctamente');
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al insertar datos masivos: ' . $e->getMessage());
            return false;
        }
    }

    public function insertarDatosMasivos2(array $datosMapeados)
    {
        $tamanoLote = 1000;
        $connection = DB::connection('pgsql-mapuche');
        try {
            $connection->beginTransaction();

            foreach (array_chunk($datosMapeados, $tamanoLote) as $lote) {
                $placeholders = implode(',', array_fill(0, count($lote[0]), '?'));

                $query = "INSERT INTO suc.afip_relaciones_activas (" . implode(',', array_keys($lote[0])) . ") VALUES ($placeholders)";
                // dd($query);
                $statement = $connection->getPdo()->prepare($query);

                foreach ($lote as $row) {
                    $statement->execute(array_values($row));
                }
            }

            $connection->commit();

            Log::info('Se importaron los datos correctamente');
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al insertar datos masivos: ' . $e->getMessage());
            return false;
        }
    }

    public function mapearDatosAlModelo(array $linea)
    {
        return AfipRelacionesActivas::mapearDatosAlModelo($linea);
    }
}
