<?php

namespace App\Services;

use App\Models\ProcessLog;
use App\Services\ProcessLogService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

/**
 * Provides a service for managing the workflow of a process.
 *
 * The WorkflowService class is responsible for managing the steps of a workflow process, including starting the process, getting the current step, completing steps, and determining the next step. It also provides methods for getting the steps of the workflow and generating URLs for each step.
 *
 * @property ProcessLogService $processLogService The service for managing process logs.
 */
class WorkflowService
{
    protected $processLogService;

    public function __construct(ProcessLogService $processLogService)
    {
        $this->processLogService = $processLogService;
    }

    public function getSteps()
    {
        return [
            // 'subir_archivo_afip' => 'Subir archivo de relaciones laborales activas',
            // 'subir_archivo_mapuche' => 'Subir archivo de Mapuche SICOSS',
            // 'import_archivo_afip' => 'Importar relaciones laborales activas',
            // 'import_archivo_mapuche' => 'Importar Mapuche SICOSS',
            // 'obtener_cuils_not_in_afip' => 'Comparar y extraer CUILs',
            // 'poblar_tabla_temp_cuils' => 'Poblar tabla Mapuche MI Simplificación',
            // 'ejecutar_funcion_almacenada' => 'Obtener diferencias de CUILs',
            // 'obtener_cuils_no_insertados' => 'Exportar resultados',
            // 'exportar_txt_para_afip' => 'Exportar resultados a AFIP',
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

    public function startWorkflow(): ProcessLog
    {
        return $this->processLogService->startProcess('afip_mi_simplificacion_workflow');
    }

    /**
     * Get the current step in the workflow process.
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
        $currentStepIndex = array_search($currentStep, $steps);

        return isset($steps[$currentStepIndex + 1]) ?? null;
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
            'upload_mapuche_sicoss' => '/upload-mapuche-sicoss',
            'import_relaciones_laborales' => '/import-relaciones-laborales',
            'import_mapuche_sicoss' => '/import-mapuche-sicoss',
            'compare_and_extract' => '/compare-and-extract',
            'populate_mapuche_simplificacion' => '/populate-mapuche-simplificacion',
            'get_cuil_differences' => '/get-cuil-differences',
            'export_results' => '/export-results'
        ];

        return $urls[$step] ?? '/';
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
