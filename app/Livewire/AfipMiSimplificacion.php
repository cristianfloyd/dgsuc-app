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



    public function boot(WorkflowService $workflowService, ProcessLogService $processLogService)
    {
        $this->workflowService = $workflowService;
        $this->processLogService = $processLogService;
    }

    public function mount()
    {

        // Intentar obtener el proceso más reciente
        $this->currentProcess = $this->processLogService->getLatestProcess();

        // Si no existe un proceso, crear uno nuevo
        if ($this->currentProcess) {
            $this->currentStep = $this->workflowService->startWorkflow();
        }

        // Almacenar el ID del proceso en lugar de la instancia completa
        $this->processLogId = $this->currentProcess->id;

        // Ahora podemos obtener el paso actual de forma segura
        $this->getStepAndCurrentStep();
    }


    public function render()
    {
        $processLog = ProcessLog::find($this->processLogId);

        return view('livewire.afip-mi-simplificacion', [
            'steps' => $this->steps,
            'currentStep' => $this->currentStep,
            'processLog' => $processLog,
            'processLogId' => $this->processLogId,
        ]);
    }

    public function getStepAndCurrentStep()
    {
        $this->steps = $this->workflowService->getSteps();
        $this->currentStep = $this->workflowService->getCurrentStep($this->currentStep);
    }


}
