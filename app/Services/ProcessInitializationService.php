<?php

namespace app\Services;

use App\Models\ProcessLog;
use App\Services\ProcessLogService;
use App\Services\WorkflowService;

class ProcessInitializationService
{
    protected $workflowService;

    protected $processLogService;

    public function __construct(WorkflowService $workflowService, ProcessLogService $processLogService)
    {
        $this->workflowService = $workflowService;
        $this->processLogService = $processLogService;
    }

    /** Inicia un nuevo proceso de registro.
     *
     * Este método crea un nuevo registro de proceso, establece su estado como 'iniciado', obtiene los pasos del flujo de trabajo y guarda el registro.
     *
     * @return ProcessLog El nuevo registro de proceso creado.
     */
    public function initializeNewProcess(string $processName): ProcessLog
    {
        $processLog = new ProcessLog();
        $processLog->process_name = $processName;
        $processLog->status = 'in_progress';
        $processLog->steps = $this->workflowService->getSteps();
        $processLog->save();

        return $processLog;
    }

    /** Inicializa o recupera el último proceso de registro.
     *
     * Si no hay un proceso actual definido, se inicia un nuevo proceso y se guarda.
     * Si no hay un paso actual definido, se inicia el flujo de trabajo y se guarda el proceso.
     *
     * @return ProcessLog El proceso actual o recién iniciado.
     */
    public function initializeOrGetLatestProcess(): ProcessLog
    {
        $currentProcess = $this->processLogService->getLatestProcess();
        /**
         * Inicia un nuevo proceso si no hay un proceso actual definido.
         * Si no hay un proceso actual, se inicia un nuevo proceso y se guarda.
         */
        if (!$currentProcess) {
            $currentProcess = $this->processLogService->startProcess('afip_mi_simplificacion_workflow');
        }

        $currentStep = $this->workflowService->getCurrentStep($currentProcess);
        /**
         * Inicia el flujo de trabajo si no hay un paso actual definido.
         * Si no hay un paso actual, se inicia el flujo de trabajo y se guarda el proceso.
         */
        if (!$currentStep) {
            $currentStep = $this->workflowService->startWorkflow();
            $currentProcess->save();
        }

        return $currentProcess;
    }
}
