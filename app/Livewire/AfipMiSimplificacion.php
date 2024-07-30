<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ProcessLog;
use App\Services\WorkflowService;
use App\Services\ProcessLogService;
use Illuminate\Support\Facades\Log;
use App\Services\ProcessInitializationService;
use Livewire\Attributes\On;

class AfipMiSimplificacion extends Component
{
    // public $processLog;
    public $processLogId;
    public $currentProcess;
    public $currentStep;
    public $steps;
    public $processFinished = false;
    public $showParaMiSimplificacion = false;

    protected $workflowService;
    protected $processLogService;
    protected $processInitializationService;



    public function boot(WorkflowService $workflowService, ProcessLogService $processLogService, ProcessInitializationService $processInitializationService)
    {
        $this->workflowService = $workflowService;
        $this->processLogService = $processLogService;
        $this->processInitializationService = $processInitializationService;
    }

    public function mount()
    {
        $this->currentProcess = $this->processInitializationService->initializeOrGetLatestProcess();
        $this->processLogId = $this->currentProcess->id;
        $this->getStepsAndCurrentStep();
        $this->processFinished = $this->isProcessFinished();
    }

    #[On('proceso-iniciado')]
    public function handleProcesoIniciado()
    {
        $this->getStepsAndCurrentStep();
        $this->processFinished = false;
    }

    #[On('proceso-terminado')]
    public function handleProcesoTerminado()
    {
        $this->processFinished = true;
        $this->getStepsAndCurrentStep();
    }

    #[On('paso-completado')]
    public function handlePasoCompletado()
    {
        $this->getStepsAndCurrentStep();
    }

    public function startProcesss()
    {
        if ($this->canStartProcess()) {
            $this->currentProcess = $this->processInitializationService->initializeNewProcess();
            $this->processLogId = $this->currentProcess->id;
            $this->getStepsAndCurrentStep();
            $this->processFinished = false;
            $this->currentProcess->save();
            $this->dispatch('proceso-iniciado');
        }
    }


    public function endProcess()
    {
        if ($this->canEndProcess()) {
            $this->processLogService->completeProcess($this->currentProcess);
            $this->processFinished = true;
            $this->currentProcess->status = 'completed';
            $this->currentProcess->save();
            $this->dispatch('proceso-terminado');
        }
    }

    private function canStartProcess()
    {
        return !$this->currentProcess || $this->currentProcess->status === 'completed';
    }

    private function canEndProcess()
    {
        return $this->currentProcess && $this->currentProcess->status === 'in_progress' && $this->allStepsCompleted();
    }

    private function allStepsCompleted()
    {
        return collect($this->currentProcess->steps)->every(fn ($step) => $step === 'completed');
    }

    public function showSteps()
    {
        return $this->steps;
    }

    /** Determina si el proceso actual ha sido completado.
     *
     * Este método utiliza el servicio de flujo de trabajo (WorkflowService) para verificar si el proceso actual ha sido completado.
     *
     * @return bool Verdadero si el proceso ha sido completado, falso en caso contrario.
     */
    public function isProcessFinished(): bool
    {
        return $this->workflowService->isProcessCompleted($this->currentProcess);
    }

    public function showParaMiSimplificacion()
    {
        if ($this->isProcessFinished()) {
            $this->showParaMiSimplificacion = true;
        }
    }


    public function getStepsAndCurrentStep(): void
    {
        $this->steps = $this->workflowService->getSteps();
        $this->currentStep = $this->workflowService->getCurrentStep($this->currentProcess);
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
}
