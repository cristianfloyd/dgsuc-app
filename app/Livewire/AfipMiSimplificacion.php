<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ProcessLog;
use App\Services\WorkflowService;
use App\Services\ProcessLogService;
use Illuminate\Support\Facades\Log;

class AfipMiSimplificacion extends Component
{
    // public $processLog;
    public $processLogId;
    public $currentProcess;
    public $currentStep;
    public $steps;

    protected $workflowService;
    protected $processLogService;




    public function mount(WorkflowService $workflowService, ProcessLogService $processLogService)
    {
        log::info("Mounting AfipMiSimplificacion component");
        $this->workflowService = $workflowService;
        $this->processLogService = $processLogService;

        // Intentar obtener el proceso más reciente
        $this->currentProcess = $this->processLogService->getLatestProcess();

        // Si no existe un proceso, crear uno nuevo
        if ($this->currentProcess) {
            $this->currentStep = $this->workflowService->startWorkflow();
        }

        // Almacenar el ID del proceso en lugar de la instancia completa
        $this->processLogId = $this->currentProcess->id;

        // Ahora podemos obtener el paso actual de forma segura
        $this->currentStep = $this->workflowService->getCurrentStep($this->currentStep);
        $this->steps = $this->workflowService->getSteps();
        Log::info("Current step: {$this->currentStep}");
        Log::info("Steps: " . json_encode($this->steps));
    }


    public function render(WorkflowService $workflowService, ProcessLogService $processLogService)
    {
        $processLog = ProcessLog::find($this->processLogId);
        $steps = $workflowService->getSteps();

        return view('livewire.afip-mi-simplificacion', [
            'steps' => $steps,
            'currentStep' => $this->currentStep,
            'processLog' => $processLog,
            'processLogId' => $this->processLogId,
        ]);
    }

    public function goToStep($step, WorkflowService $workflowService)
    {
        $processLog = ProcessLog::find($this->processLogId);
        // dd($step, $this->currentStep, $this->processLogId, $this->currentProcess, $processLog);
        if(
            $workflowService->isStepCompleted($processLog, $step) || $step === $this->currentStep)
        {
            // dd($step, 'siguiente paso');
            return redirect($workflowService->getStepUrl($step));
        }
    }
}
