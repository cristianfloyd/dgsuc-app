<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TempTableService extends DatabaseService
{
    protected $tempTableName = 'tabla_temp_cuils';

    /**
     * Inserta un conjunto de CUILs en la tabla temporal.
     *
     * Esta función recibe un array de CUILs y los inserta en lotes de 1000 registros en la tabla temporal 'tabla_temp_cuils'.
     * Si ocurre alguna excepción durante el proceso, se realiza un rollback de la transacción y se registra el error en el log.
     *
     * @param array $cuils Array de CUILs a insertar en la tabla temporal.
     * @return bool Verdadero si la inserción se realizó correctamente, falso en caso contrario.
     */
    public function populateTempTable(array $cuils): bool
    {
        $connection = DB::connection('pgsql-mapuche');

        try {
            $connection->beginTransaction();

            foreach (array_chunk($cuils, 1000) as $chunk) {
                $connection->table($this->tempTableName)->insert(
                    array_map(function ($cuil) {
                        return ['cuil' => $cuil];
                    }, $chunk)
                );
            }

            $connection->commit();
            Log::info('Datos insertados correctamente en la tabla temporal.');
            return true;
        } catch (\Exception $e) {
            // Manejar la excepción aquí
            $connection->rollBack();
            Log::error('Error al insertar datos en la tabla temporal: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Limpia la tabla temporal de CUILs.
     *
     * Esta función elimina todos los registros de la tabla temporal 'tabla_temp_cuils'. Si ocurre alguna excepción durante el proceso, se registra el error en el log.
     *
     * @return bool Verdadero si la limpieza se realizó correctamente, falso en caso contrario.
     */
    public function clearTempTable(): bool
    {
        try {

            DB::connection('pgsql-mapuche')->table($this->tempTableName)->truncate();
            Log::info('Tabla temporal limpiada correctamente.');
            return true;

        } catch (\Exception $e) {
            // Manejar la excepción aquí
            Log::error('Error al limpiar la tabla temporal: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el número de registros en la tabla temporal.
     *
     * Esta función devuelve el número total de registros almacenados en la tabla temporal 'tabla_temp_cuils'.
     *
     * @return int El número de registros en la tabla temporal.
     */
    public function getTempTableCount(): int
    {
        return DB::connection('pgsql-mapuche')->table($this->tempTableName)->count();
    }
}
