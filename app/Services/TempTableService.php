<?php

namespace App\Services;

use App\Models\TablaTempCuils;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TempTableService extends DatabaseService
{
    use MapucheConnectionTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new TablaTempCuils();
    }

    public function getFullTableName(): string
    {
        return $this->model->getFullTableName();
    }

    /**
     * Inserta un conjunto de CUILs en la tabla temporal.
     *
     * Esta función recibe un array de CUILs y los inserta en lotes de 1000 registros en la tabla temporal 'tabla_temp_cuils'.
     * Si ocurre alguna excepción durante el proceso, se realiza un rollback de la transacción y se registra el error en el log.
     *
     * @param array $cuils Array de CUILs a insertar en la tabla temporal.
     *
     * @return bool Verdadero si la inserción se realizó correctamente, falso en caso contrario.
     */
    public function populateTempTable(array $cuils): bool
    {
        try {
            DB::connection($this->getConnectionName())->beginTransaction();

            $tableName = $this->getFullTableName();

            // Verificar si la tabla existe
            if (!Schema::connection($this->getConnectionName())->hasTable($tableName)) {
                // Si no existe, crearla
                $this->model->createTable();
            } else {
                // Si existe, vaciarla
                $this->clearTempTable();
            }

            // Insertar los nuevos datos
            foreach (array_chunk($cuils, 1000) as $chunk) {
                DB::connection($this->getConnectionName())
                    ->table($tableName)
                    ->insert(array_map(function ($cuil) {
                        return ['cuil' => $cuil];
                    }, $chunk));
            }

            DB::connection($this->getConnectionName())->commit();
            Log::info("Datos insertados correctamente en la tabla {$tableName}.");
            return true;

        } catch (\Exception $e) {
            DB::connection($this->getConnectionName())->rollBack();
            Log::error("Error al insertar datos en la tabla {$this->model->getFullTableName()}: " . $e->getMessage());
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
            $tableName = $this->getFullTableName();
            DB::connection($this->getConnectionName())
                ->table($tableName)
                ->truncate();

            Log::info("Tabla {$tableName} limpiada correctamente.");
            return true;

        } catch (\Exception $e) {
            // Manejar la excepción aquí
            Log::error("Error al limpiar la tabla {$this->model->getFullTableName()}: " . $e->getMessage());
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
        return DB::connection($this->getConnectionName())
            ->table($this->getFullTableName())
            ->count();
    }
}
