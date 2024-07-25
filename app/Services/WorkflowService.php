<?php

namespace App\Services;

use App\Models\ProcessLog;
use App\Services\ProcessLogService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

/**
 * Proporciona un servicio para gestionar el flujo de trabajo de un proceso.
 *
 * La clase WorkflowService es responsable de administrar los pasos de un proceso de flujo de trabajo, incluido iniciar el proceso, obtener el paso actual, completar los pasos y determinar el siguiente paso. También proporciona métodos para obtener los pasos del flujo de trabajo y generar URL para cada paso.
 *
 * @property ProcessLogService $processLogService El servicio para gestionar registros de procesos.
 */
class WorkflowService
{
    protected $processLogService;

    public function __construct(ProcessLogService $processLogService)
    {
        $this->processLogService = $processLogService;
    }

    /**
     * Devuelve una matriz asociativa de los pasos del proceso de flujo de trabajo.
     * Las keys son los nombres de los pasos y los valores son las descripciones de los pasos(stpes).
     *
     * @return array Una matriz asociativa de pasos del flujo de trabajo.
     */
    public function getSteps()
    {
        return [
            'subir_archivo_afip' => 'Subir archivo de relaciones laborales activas',
            'subir_archivo_mapuche' => 'Subir archivo de Mapuche SICOSS',
            'import_archivo_afip' => 'Importar relaciones laborales activas',
            'import_archivo_mapuche' => 'Importar Mapuche SICOSS',
            'obtener_cuils_not_in_afip' => 'Comparar y extraer CUILs',
            'poblar_tabla_temp_cuils' => 'Poblar tabla Mapuche MI Simplificación',
            'ejecutar_funcion_almacenada' => 'Obtener diferencias de CUILs',
            'obtener_cuils_no_insertados' => 'Cuils no insertados',
            'exportar_txt_para_afip' => 'Exportar resultados a AFIP',
        ];
    }

    /**
     * Inicia un nuevo proceso de flujo de trabajo y devuelve la instancia de ProcessLog creada.
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
     * Recupera el último registro del proceso de flujo de trabajo, si no se completó o falló.
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

        // Verificar si el proceso mas reciente esta completado o fallido
        if (in_array($latestProcess->status, [ 'completed','failed'])) {
            return null; //Retorna null si el proceso esta completado o fallido
        }

        return $latestProcess;
    }

    /**
     * Obtenga el paso actual en el proceso de flujo de trabajo.
     *
     * @param ProcessLog $processLog The process log instance.
     * @return string The current step in the workflow process, or 'completed' if all steps are completed.
     */
    public function getCurrentStep(ProcessLog $processLog)
    {
        $steps = $processLog->steps;
        foreach ($steps as $step => $status) {
            if ($status !== 'completed') {
                return $step;
            }
        }
        return 'completed';
    }

    /**
     * Completa un paso en el proceso de flujo de trabajo y actualiza el siguiente paso si está disponible.
     *
     * Este método actualiza el estado del paso especificado en la instancia de ProcessLog proporcionada a "completed". Luego recupera el siguiente paso en el proceso de flujo de trabajo y actualiza el estado de ese paso a "in_progress".
     *
     * @param ProcessLog $processLog La instancia de registro de proceso que se actualizará.
     * @param string $step El nombre del paso a marcar como completado.
     */
    public function completeStep(ProcessLog $processLog, string $step)
    {
        $this->processLogService->updateStep($processLog, $step, 'completed');
        Log::info("Paso completado: {$step}", ['process_id' => $processLog->id]);

        $nextStep = $this->getNextStep($step);
        if ($nextStep) {
            $this->processLogService->updateStep($processLog, $nextStep, 'in_progress');

        }
    }

    /**
 * Obtiene el siguiente paso de el flujo de trabajo.
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

        $processLog = $this->getLatestWorkflow();
        Log::info("getNextStep() -> Paso actual: {$currentStep}", ['process_id' => $processLog->id]);
        $pasos = $steps[$currentStepIndex + 1] ?? null;
        Log::info("getNextStep() -> Siguiente paso: {$pasos}", ['process_id' => $processLog->id]);
        return $pasos;
    }

    /**
 * Verifica si un paso del proceso ha sido completado.
 *
 * @param ProcessLog $processLog El registro del proceso.
 * @param string $step El paso a verificar.
 * @return bool Verdadero si el paso ha sido completado, falso en caso contrario.
 */
    public function isStepCompleted(ProcessLog $processLog, string $step): bool
    {
        return $processLog->steps[$step] === 'completed';
    }


    /**
 * Obtiene la URL de un paso del flujo de trabajo.
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
            'obtener_cuils_not_in_afip' => '/compare-cuils',
            'poblar_tabla_temp_cuils' => '/compare-cuils',
            'ejecutar_funcion_almacenada' => '/compare-cuils',
            'obtener_cuils_no_insertados' => '/compare-cuils',
            'exportar_txt_para_afip' => '/export-results'
        ];

        return $urls[$step] ?? '/';
    }
}
