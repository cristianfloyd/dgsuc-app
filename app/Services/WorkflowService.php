<?php

namespace App\Services;

use App\Models\ProcessLog;
use App\Services\ProcessLogService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use App\Contracts\WorkflowServiceInterface;
use Illuminate\Contracts\Events\Dispatcher;


/** Proporciona un servicio para gestionar el flujo de trabajo de un proceso.
 *
 * La clase WorkflowService es responsable de administrar los pasos de un proceso de flujo de trabajo, incluido iniciar el proceso, obtener el paso actual, completar los pasos y determinar el siguiente paso. También proporciona métodos para obtener los pasos del flujo de trabajo y generar URL para cada paso.
 *
 * @property ProcessLogService $processLogService El servicio para gestionar registros de procesos.
 */
class WorkflowService implements WorkflowServiceInterface
{
    protected $processLogService;


    public function __construct(ProcessLogService $processLogService)
    {
        $this->processLogService = $processLogService;
    }


    /**  Inicia un nuevo Proceso de flujo de trabajo y devuelve la instancia de ProcessLog creada.
     *
     * Este método inicializa un nuevo proceso de flujo de trabajo creando un nuevo registro ProcessLog con el tipo de proceso 'afip_mi_simplificacion_workflow'. Se devuelve la instancia de ProcessLog creada.
     *
     * @return ProcessLog La instancia de ProcessLog creada para el nuevo proceso de flujo de trabajo.
     */
    public function startWorkflow(): ProcessLog
    {
        return $this->processLogService->startProcess('afip_mi_simplificacion_workflow');
    }

    /**
     * Reinicia el flujo de trabajo de un proceso.
     *
     * Este método reinicia el flujo de trabajo de un proceso estableciendo todos los pasos en estado "pending".
     * Se actualiza la instancia de ProcessLog proporcionada y se guarda en la base de datos.
     * También se registra el evento de reinicio del flujo de trabajo en el log.
     *
     * @param ProcessLog $processLog La instancia de registro de proceso que se reiniciará.
     * @return void
     */
    public function resetWorkflow(ProcessLog $processLog): void
    {
        $steps = array_keys($this->getSteps());
        $updatedSteps = [];
        foreach ($steps as $step) {
            $updatedSteps[$step] = 'pending';
        }
        $processLog->steps = $updatedSteps;
        $processLog->save();

        Log::info("Flujo de trabajo reiniciado", ['process_id' => $processLog->id]);
    }


    /**  Recupera el último registro del proceso de flujo de trabajo, si no se completó o falló.
     *
     * Este método recupera el registro de proceso más reciente de ProcessLogService. Si el último proceso no está en un estado completo o fallido, devuelve el proceso log instance. Otherwise, it returns null.
     *
     * @return ProcessLog|null El último registro del proceso de flujo de trabajo, o nulo si el último proceso se completó o falló.
     */
    public function getLatestWorkflow(): ?ProcessLog
    {
        $latestProcess = $this->processLogService->getLatestProcess();
        if (!$latestProcess) {
            return null;
        }
        return $latestProcess;
    }


    /* Devuelve una matriz asociativa de los pasos del proceso de flujo de trabajo.
     * Las keys son los nombres de los pasos y los valores son las descripciones de los pasos(stpes).
     *
     * @return array Una matriz asociativa de pasos del flujo de trabajo.
     */
    public function getSteps(): array
    {
        return [
            'subir_archivo_afip' => 'Subir archivo de relaciones laborales activas',
            'subir_archivo_mapuche' => 'Subir archivo de Mapuche SICOSS',
            'import_archivo_afip' => 'Importar relaciones laborales activas',
            'import_archivo_mapuche' => 'Importar Mapuche SICOSS',
            'obtener_cuils_not_in_afip' => 'CUILs no existentes en AFIP',
            'poblar_tabla_temp_cuils' => 'Poblar tabla temp de CUILs',
            'ejecutar_funcion_almacenada' => 'diferencias de CUILs',
            'obtener_cuils_no_insertados' => 'Cuils no insertados',
            'exportar_txt_para_afip' => 'Exportar resultados a AFIP',
        ];
    }



    /** Obtiene el paso actual en el proceso de flujo de trabajo.
     *
     * Este método recorre los pasos del registro de proceso proporcionado y devuelve el primer paso que no está en estado "completed". Si todos los pasos están en estado "completed", devuelve "null".
     *
     * @param ProcessLog $processLog La instancia de registro de proceso.
     * @return string|null El paso actual en el proceso de flujo de trabajo, o "null" si todos los pasos están completados.
     */
    public function getCurrentStep(ProcessLog $processLog): string|null
    {
        $steps = $processLog->steps;
        foreach ($steps as $step => $status) {
            if ($status !== 'completed') {
                Log::info("Return en getCurrentStep: $step");
                return $step;
            }
        }
        return null;
    }



    /* Actualiza el estado de un paso en el registro del proceso de flujo de trabajo.
     *
     * Este método actualiza el estado del paso especificado en la instancia de ProcessLog proporcionada al estado indicado. También realiza algunas acciones adicionales, como:
     * - Registrar el cambio de estado en el log.
     * - Si el paso se marca como "completed", obtener el siguiente paso en el flujo de trabajo y actualizarlo a "in_progress".
     *
     * @param ProcessLog $processLog La instancia de registro de proceso que se actualizará.
     * @param string $step El nombre del paso a actualizar.
     * @param string $status El nuevo estado del paso (por ejemplo, "completed", "in_progress", etc.).
     */
    public function updateStep(ProcessLog $processLog, string $step, string $status)
    {
        // Actualiza el estado del step en el workflow
        $steps = $processLog->steps;
        if (isset($steps[$step])) {
            $steps[$step] = $status;

            // Actualizar el ProcessLog
            $this->processLogService->updateStep($processLog, $step, $status);

            Log::info("Paso actualizado en WorkflowService: $step - $status", ['process_id' => $processLog->id]);
        } else {
            Log::warning("Paso no encontrado en WorkflowService: $step", ['process_id' => $processLog->id]);
        }
    }


    /** Completa un paso en el proceso de flujo de trabajo y no actualiza el siguiente paso si está disponible.
     *
     * Este método actualiza el estado del paso especificado en la instancia de ProcessLog proporcionada a "completed". Luego recupera el siguiente paso en el proceso de flujo de trabajo.
     *
     * @param ProcessLog $processLog La instancia de registro de proceso que se actualizará.
     * @param string $step El nombre del paso a marcar como completado.
     */
    public function completeStep(ProcessLog $processLog, string $step): void
    {
        $this->updateStep($processLog, $step, 'completed');
        Log::info("Paso completado: {$step}", ['process_id' => $processLog->id]);
    }


    /** Obtiene el siguiente paso de el flujo de trabajo.
     *
     * @param string $currentStep El paso actual en el flujo de trabajo.
     * @return string|null El siguiente paso en el flujo de trabajo, o null si no hay más pasos.
     */
    public function getNextStep(string $currentStep): ?string
    {
        $steps = array_keys($this->getSteps());
        if (($currentStepIndex = array_search($currentStep, $steps)) === false) {
            throw new \InvalidArgumentException("Paso no válido: {$currentStep}");
        }

        Log::info("getNextStep() -> Paso actual: {$currentStep}");
        /**
         * Obtiene el siguiente paso en el flujo de trabajo.
         *
         * Si el paso actual es el último en el flujo de trabajo, devuelve null.
         *
         * @return string|null El siguiente paso en el flujo de trabajo, o null si no hay más pasos.
         */
        $pasos = $steps[$currentStepIndex + 1] ?? null;
        Log::info("getNextStep() -> Siguiente paso: {$pasos}");
        return $pasos;
    }

    /** Verifica si un paso del proceso ha sido completado.
     *
     * @param ProcessLog $processLog El registro del proceso.
     * @param string $step El paso a verificar.
     * @return bool Verdadero si el paso ha sido completado, falso en caso contrario.
     */
    public function isStepCompleted(ProcessLog $processLog, string $step): bool
    {
        return $processLog->steps[$step] === 'completed';
    }


    /** Obtiene la URL de un paso del flujo de trabajo.
     *
     * @param string $step El nombre del paso del flujo de trabajo.
     * @return string La URL del paso del flujo de trabajo.
     */
    public function getStepUrl(string $step): string
    {
        // return route('workflow.step', ['step' => $step]);
        $urls = [
            'subir_archivo_afip' => '/afip/subir-archivo',
            'subir_archivo_mapuche' => '/afip/subir-archivo',
            'import_archivo_afip' => '/afip/relaciones-activas',
            'import_archivo_mapuche' => '/afip/mapuchesicoss',
            'obtener_cuils_not_in_afip' => '/afip/compare-cuils',
            'poblar_tabla_temp_cuils' => '/afip/compare-cuils',
            'ejecutar_funcion_almacenada' => '/afip/compare-cuils',
            'obtener_cuils_no_insertados' => '/afip/compare-cuils',
            'exportar_txt_para_afip' => '/export-results'
        ];

        return $urls[$step] ?? '/';
    }

    /** Obtiene los sub-pasos de un paso específico del flujo de trabajo.
     *
     * @param string $step El nombre del paso del flujo de trabajo.
     * @return array Los sub-pasos del paso especificado.
     */
    public function getSubSteps($step): array
    {
        $subSteps = [
            'poblar_tabla_temp_cuils' => [
                'verificar_existencia_tabla',
                'crear_tabla_si_no_existe',
                'borrar_datos_si_existen',
                'insertar_datos'
            ]
        ];
        return $subSteps[$step] ?? [];
    }

    /**
     * Verifica si todos los pasos del proceso están completados y actualiza el estado del ProcessLog si es necesario.
     *
     * Este método comprueba si todos los pasos en el ProcessLog están marcados como 'completed'.
     * Si todos los pasos están completados y el estado del ProcessLog no es 'completed',
     * actualiza el estado a 'completed' y establece la fecha de finalización.
     *
     * @param ProcessLog $processLog El registro del proceso a verificar y potencialmente actualizar.
     * @return bool Retorna true si todos los pasos están completados, false en caso contrario.
     */
    public function isProcessCompleted(ProcessLog $processLog): bool
    {
        $allCompleted = array_reduce(
            $processLog->steps,
            function ($carry, $step) {
                return $carry && $step === 'completed';
            },
            true
        );
        
        if ($allCompleted && $processLog->status !== 'completed') {
            $processLog->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);
        } elseif (!$allCompleted){
            return false;
        }

        return $allCompleted;
    }
}
