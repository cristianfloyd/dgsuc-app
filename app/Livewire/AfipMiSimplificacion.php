<?php

namespace App\Livewire;

use App\Traits\MessageTrait;
use Livewire\Component;
use App\Models\ProcessLog;
use App\Services\WorkflowService;
use App\Services\ProcessLogService;
use Illuminate\Support\Facades\Log;
use App\Services\ProcessInitializationService;
use Livewire\Attributes\On;

class AfipMiSimplificacion extends Component
{
    // use MessageTrait;
    public $processLogId;
    public $currentProcess;
    public $currentStep;
    public $steps;
    public $processFinished = false;
    public $ParaMiSimplificacion = false;
    public $message = null;

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
    public function handleProcesoIniciado(): void
    {
        $this->getStepsAndCurrentStep();
        $this->processFinished = false;
        $this->showMessage('Proceso iniciado correctamente');
    }

    #[On('proceso-terminado')]
    public function handleProcesoTerminado(): void
    {
        $this->processFinished = true;
        $this->getStepsAndCurrentStep();
        $this->showMessage('Proceso terminado correctamente');
    }

    #[On('paso-completado')]
    public function handlePasoCompletado(): void
    {
        $this->getStepsAndCurrentStep();
        $this->showMessage('Paso completado correctamente');
    }

/** Inicia un nuevo proceso.
 *
 * Este método verifica si se puede iniciar un nuevo proceso, y si es así, crea un nuevo proceso, actualiza el proceso actual,
 * actualiza la lista de pasos y notifica que el proceso ha sido iniciado.
 *
 * @return void
 */
    public function startProcess(): void
    {
        if ($this->canStartProcess()) {
            $processName = 'afip_mi_simplificacion_workflow';
            $this->currentProcess = $this->processInitializationService->initializeNewProcess($processName);
            $this->processLogId = $this->currentProcess->id;
            $this->getStepsAndCurrentStep();
            $this->processFinished = false;
            $this->currentProcess->save();
            $this->dispatch('proceso-iniciado');
        }
    }

/** Finaliza el proceso actual.
 *
 * Este método verifica si se puede finalizar el proceso actual, y si es así, marca el proceso como completado,
 * actualiza el estado del proceso y notifica que el proceso ha sido finalizado.
 *
 * @return void
 */
    public function endProcess(): void
    {
        if ($this->canEndProcess()) {
            $this->processLogService->completeProcess($this->currentProcess);
            $this->processFinished = true;
            $this->currentProcess->status = 'completed';
            $this->currentProcess->save();
            $this->dispatch('proceso-terminado');
        }
    }

    /** Marca un paso como completado en el proceso actual.
     *
     * Este método utiliza el servicio de flujo de trabajo (WorkflowService) para marcar un paso como completado en el proceso actual.
     *
     * @param mixed $step El paso a marcar como completado.
     * @return void
     */
    public function markStepAsCompleted($step): void
    {
        $this->workflowService->completeStep($this->currentProcess, $step);
        $this->dispatch('paso-completado');
    }

/** Muestra un mensaje al usuario.
 *
 * Este método agrega un mensaje a la lista de mensajes del componente.
 *
 * @param string $message El mensaje a mostrar.
 * @return void
 */
    public function showMessage($message): void
    {
        if(emptyTraversable()){
            return;
        }
        $this->message = $message;
        $this->dispatch('show-message', ['message' => $this->message]);
    }

/** Verifica si se puede iniciar un nuevo proceso.
 *
 * Este método verifica si el proceso actual no existe o si ha sido completado.
 *
 * @return bool Verdadero si se puede iniciar un nuevo proceso, falso en caso contrario.
 */
    private function canStartProcess(): bool
    {
        return $this->currentProcess === null || ($this->currentProcess !== null && $this->currentProcess->status === 'completed');
    }

/** Verifica si se puede finalizar el proceso actual.
 *
 * Este método verifica si el proceso actual existe, está en progreso y todos los pasos han sido completados.
 *
 * @return bool Verdadero si se puede finalizar el proceso actual, falso en caso contrario.
 */
    private function canEndProcess(): bool
    {
        return $this->currentProcess !== null && $this->currentProcess->status === 'in_progress' && $this->allStepsCompleted();
    }


    /** Verifica si todos los pasos del proceso actual han sido completados.
     *
     * Este método utiliza la colección de pasos del proceso actual y verifica si todos ellos tienen el estado 'completed'.
     *
     * @return bool Verdadero si todos los pasos han sido completados, falso en caso contrario.
     */
    private function allStepsCompleted(): bool
    {
        if ($this->currentProcess->steps === null) {
            return false;
        }
        return collect($this->currentProcess->steps)->every(fn ($step) => $step === 'completed');
    }


/** Retorna la lista de pasos del proceso actual.
 *
 * Este método devuelve la colección de pasos del proceso actual, que se utiliza para mostrar la progresión del proceso.
 *
 * @return array La lista de pasos del proceso actual.
 */
    public function showSteps(): array
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

/** Muestra el proceso de para mi simplificación si el proceso actual ha sido completado.
 *
 * Este método verifica si el proceso actual ha sido completado y si es así, muestra el proceso de para mi simplificación.
 *
 * @return void
 */
    public function showParaMiSimplificacion(): void
    {
        if ($this->isProcessFinished()) {
            $this->ParaMiSimplificacion = true;
        }
    }

/** Obtiene la lista de pasos y el paso actual del proceso actual.
 *
 * Este método utiliza el servicio de flujo de trabajo (WorkflowService) para obtener la lista de pasos y el paso actual del proceso actual.
 *
 * @return void
 */
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

