<?php

namespace App\Services;

use App\Models\ProcessLog;
use Illuminate\Support\Facades\Log;

class ProcessLogService
{


    /**
     * Inicia un nuevo proceso y crea un registro en la base de datos.
     *
     * @param string $processName
     * @return ProcessLog
     */
    public function startProcess(string $processName)
    {
        $processLog = ProcessLog::create([
            'process_name' => $processName,
            'status' => 'in_progress',
            'steps' => [
                'subir_archivo_afip' => 'pending',              // 1
                'subir_archivo_mapuche' => 'pending',           // 2
                'import_archivo_afip' => 'pending',             // 3
                'import_archivo_mapuche' => 'pending',          // 4
                'obtener_cuils_not_in_afip' => 'pending',       // 5.1
                'poblar_tabla_temp_cuils' => 'pending',         // 5.2
                'ejecutar_funcion_almacenada' => 'pending',     // 6
                'obtener_cuils_no_insertados' => 'pending',     // 7
                'exportar_txt_para_afip' => 'pending'           // 8
            ],
            'started_at' => now(),
        ]);

        Log::info("Proceso iniciado: {$processName}", ['process_id' => $processLog->id]);

        return $processLog;
    }

    /**
     * Actualiza el estado de un paso específico del proceso.
     *
     * @param ProcessLog $processLog
     * @param string $step
     * @param string $status
     * @return void
     */
    public function updateStep(ProcessLog $processLog, string $step, string $status)
    {
        if (!is_array($processLog->steps)) {
            throw new \InvalidArgumentException('Los pasos del proceso deben ser un arreglo');
        }

        $steps = $processLog->steps;

        if (!isset($steps[$step])) {
            throw new \OutOfBoundsException("Paso no encontrado: $step");
        }

        $steps[$step] = $status;

        if (!$processLog->update(['steps' => $steps])) {
            throw new \Exception('No se pudo actualizar el registro del proceso');
        }

        Log::info("Paso actualizado: $step - $status", ['process_id' => $processLog->id]);
    }


    /**
    * Verificar si todos los pasos del proceso han sido completados
    *
    * @param array $steps
    * @return bool
    */
    private function allStepsCompleted(array $steps): bool
    {
        return collect($steps)->every(function ($status) {
            return $status === 'completed';
        });
    }

    /*
    * Marca el proceso como completado
    *
    * @param ProcessLog $processLog
    * @return void
    */
    private function completeProcess(ProcessLog $processLog): void
    {
        $processLog->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
    }

    /*
    * Marca el proceso como fallido
    *
    * @param ProcessLog $processLog
    * @param string $errorMessage
    * @return void
    */
    public function FailProcess(ProcessLog $processLog, string $errorMessage): void
    {
        $processLog->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $errorMessage
        ]);

        Log::error("Proceso fallido: {$processLog->process_name} - {$errorMessage}", ['process_id' => $processLog->id]);
    }

    /**
     * Devuelve el último registro de ProcessLog.
     *
     * @return \App\Models\Mapuche\ProcessLog|null El último registro de ProcessLog, o nulo si no existe ninguno.
     */
    public function getLatestProcess(): ?ProcessLog
    {
        Log::info("Buscando el último registro de ProcessLog en ProcessLogService");
        return ProcessLog::latest()->first();
    }
}
