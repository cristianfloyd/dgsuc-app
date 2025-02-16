<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ProcessLog;
use Livewire\Attributes\On;
use App\Enums\WorkflowStatus;
use Livewire\Attributes\Computed;
use illuminate\Support\Facades\Log;
use App\Services\FileProcessingService;
use App\Contracts\MessageManagerInterface;
use App\Contracts\WorkflowServiceInterface;
use App\Contracts\FileUploadRepositoryInterface;
use App\Contracts\MapucheMiSimplificacionServiceInterface;

class AfipMiSimplificacion extends Component
{
    // use MessageTrait;
    public $showGenerateRelationsButton = false;
    public $processLogId;
    public $currentProcess;
    public $currentStep;
    public $currentStepUrl;
    public $steps;
    public $stepUrl;
    public $processFinished = false;
    public $ButtonMiSimplificacion = false;
    public $message = null;


    private WorkflowServiceInterface $workflowService;
    private MessageManagerInterface $messageManager;
    private FileUploadRepositoryInterface $fileUploadRepository;
    private FileProcessingService $fileProcessingService;
    private MapucheMiSimplificacionServiceInterface $mapucheMiSimplificacionService;

    public function boot(
        WorkflowServiceInterface $workflowService,
        MessageManagerInterface $messageManager,
        FileUploadRepositoryInterface $fileUploadRepository,
        FileProcessingService $fileProcessingService,
        MapucheMiSimplificacionServiceInterface $mapucheMiSimplificacionService,
        )
    {
        $this->workflowService = $workflowService;
        $this->messageManager = $messageManager;
        $this->fileUploadRepository = $fileUploadRepository;
        $this->fileProcessingService = $fileProcessingService;
        $this->mapucheMiSimplificacionService = $mapucheMiSimplificacionService;
    }

    public function mount()
    {
        $this->currentProcess = $this->workflowService->getLatestWorkflow();
        if (!$this->currentProcess) {
            $this->currentProcess = $this->workflowService->startWorkflow();
        }
        $this->processLogId = $this->currentProcess->id;
        $this->getStepsAndCurrentStep();
        $this->processFinished = $this->isProcessFinished();
        $this->getCurrentStepUrl();
        $this->checkShowGenerateRelationsButton();
        $this->checkMiSimplificacion();
    }

    /**
     * Agrega un mensaje al gestor de mensajes y lo envía a través de un evento.
     *
     * @param string $message El mensaje a mostrar.
     * @param string $type El tipo de mensaje (por ejemplo, 'info', 'error', etc.).
     * @return void
     */
    public function showMessage($message, $type = 'info'): void
    {
        $this->messageManager->addMessage($message, $type);
        $this->dispatch('show-message', ['message' => $message, 'type' => $type]);
    }

    public function checkMiSimplificacion()
    {
        // este metodo verifica si la tabla afip_mi_simplificacion tiene datos, y si tiene datos, muestra el boton de mi simplificacion
        $hasDatos = $this->mapucheMiSimplificacionService->isNotEmpty();

        if ($hasDatos) {
            $this->ButtonMiSimplificacion = true;
            $this->showMessage('Se han encontrado datos en Mi Simplificación', 'info');
        } else {
            $this->ButtonMiSimplificacion = false;
            $this->showMessage('No se encontraron datos en Mi Simplificación', 'info');
        }

    }

    #[On('proceso-iniciado')]
    public function handleProcesoIniciado(): void
    {
        $this->getStepsAndCurrentStep();
        $this->processFinished = false;
        $this->ButtonMiSimplificacion = false;
        $this->showMessage('Proceso iniciado correctamente');
    }

    #[On('proceso-terminado')]
    public function handleProcesoTerminado(): void
    {
        $this->processFinished = true;
        $this->ButtonMiSimplificacion = true;
        $this->showMessage('Proceso terminado correctamente');
    }

    #[On('paso-completado')]
    public function handlePasoCompletado(): void
    {
        $this->getStepsAndCurrentStep();
        $this->showMessage('Paso completado correctamente');
    }

    #[On('liquidacionSeleccionada')]
    public function handleLiquidacionSeleccionada(): void
    {

        $this->showMessage('Liquidación seleccionada correctamente');
    }

    /** Inicia un nuevo proceso.
     *
     * Este método verifica si se puede iniciar un nuevo proceso, y si es así, crea un nuevo proceso, actualiza el proceso actual,
     * actualiza la lista de pasos, notifica que el proceso ha sido iniciado y redirige a la url correspondiente.
     *
     * @return void
     */
    public function startProcess(): void
    {
        $status = $this->isNewProcessAllowed();
        if ($status) {
            $this->currentProcess = $this->workflowService->startWorkflow();
            $this->processLogId = $this->currentProcess->id;
            $this->getStepsAndCurrentStep();
            $this->processFinished = false;
            $this->currentProcess->save();
            $this->showMessage('proceso-iniciado');
            // utilizando la interface workflowServiceinterface obtener la url que corresponde al primer paso
            $this->redirect($this->currentStepUrl);
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
            $this->currentProcess->status = 'completed';
            $this->currentProcess->save();
            Log::info('Proceso finalizado correctamente');
            $this->showMessage('proceso-terminado');
            $this->processFinished = false;
        }
    }

    /**
     * Reinicia el flujo de trabajo del proceso actual.
     *
     * Este método utiliza el servicio de flujo de trabajo (WorkflowService) para reiniciar el flujo de trabajo del proceso actual.
     * Luego, actualiza el proceso actual, el paso actual y el estado de finalización del proceso.
     *
     * @return void
     */
    public function resetWorkflow(): void
    {
        Log::info("AfipMiSimplificacion->resetWorkflow");
        Log::info($this->currentProcess);
        if ($this->currentProcess) {
            $this->workflowService->resetWorkflow($this->currentProcess);
            $this->currentProcess = $this->workflowService->getLatestWorkflow();
            $this->currentStep = $this->workflowService->getCurrentStep($this->currentProcess);
            $this->processFinished = $this->workflowService->isProcessCompleted($this->currentProcess);
        }
        Log::info($this->currentProcess);
    }




    /**
     * Redirige al usuario al URL del paso actual del proceso.
     *
     * Este método establece el paso actual y luego redirige al usuario al URL correspondiente a ese paso.
     *
     * @param int $step El ID del paso actual del proceso.
     * @return void
     */
    public function goToCurrentStep($step): void
    {
        $this->currentStep = $step;
        $this->redirect($this->currentStepUrl);
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

    /**
     * Obtiene la URL del paso actual del proceso.
     *
     * Este método utiliza el servicio de flujo de trabajo (WorkflowService) para obtener la URL del paso actual del proceso actual.
     *
     * @return void
     */
    public function getCurrentStepUrl(): void
    {
        if (!$this->allStepsCompleted()) {
            $this->currentStepUrl = $this->workflowService->getStepUrl($this->currentStep);
        }
    }

    /**
     * Determina si se puede iniciar un nuevo proceso.
     *
     * @return bool Verdadero si se puede iniciar un nuevo proceso, falso en caso contrario.
     */
    private function isNewProcessAllowed(): bool
    {
        return !$this->currentProcess || $this->currentProcess->status === 'completed';
    }

    /** Verifica si se puede finalizar el proceso actual.
     *
     * Este método verifica si el proceso actual existe, está en progreso y todos los pasos han sido completados.
     *
     * @return bool Verdadero si se puede finalizar el proceso actual, falso en caso contrario.
     */
    private function canEndProcess(): bool
    {
        $status = $this->currentProcess && $this->currentProcess->status === 'in_progress' || $this->allStepsCompleted();
        // dd($this->currentProcess->status, $this->currentStep);
        return $status;
    }

    /** Determina si el proceso actual ha sido completado.
     *
     * Este método utiliza el servicio de flujo de trabajo (WorkflowService) para verificar si el proceso actual ha sido completado.
     *
     * @return bool Verdadero si el proceso ha sido completado, falso en caso contrario.
     */
    public function isProcessFinished(): bool
    {
        $status = $this->workflowService->isProcessCompleted($this->currentProcess);

        return $status;
    }

    #[Computed]
    public function getStepUrl($step): string
    {
        return $this->workflowService->getStepUrl($step);
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
        return collect($this->currentProcess->steps)->every(fn($step) => $step === 'completed');
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

    #[Computed]
    public function showResetButton()
    {
        $currentStepIndex = $this->getCurrentStepIndex();
        return $this->currentProcess !== null && $currentStepIndex !== null && $currentStepIndex !== count($this->steps) - 1;
    }


    public function getCurrentStepIndex()
    {
        $currentStep = $this->currentStep;
        $steps = $this->steps;
        $i = 0;
        foreach ($steps as $index => $step) {
            if ($index === $currentStep) {
                return $i;
            }
            $i++;
        }

        return null; // si no se encuentra el paso actual
    }



    /** Muestra el proceso de para mi simplificación si el proceso actual ha sido completado.
     *
     * Este método verifica si el proceso actual ha sido completado y si es así, muestra el proceso de para mi simplificación.
     *
     * @return void
     */
    public function toggleParaMiSimplificacion(): void
    {
        if ($this->isProcessFinished()) {
            $this->ButtonMiSimplificacion = !$this->ButtonMiSimplificacion;
        }
    }

    public function checkShowGenerateRelationsButton(): void
    {
        $afipFile = $this->fileUploadRepository->getLatestByOrigen('afip');
        $mapucheFile = $this->fileUploadRepository->getLatestByOrigen('mapuche');

        $this->showGenerateRelationsButton = $afipFile && $mapucheFile && $afipFile->process_id === $mapucheFile->process_id;
    }

    /**
     * Genera las relaciones entre los archivos AFIP y Mapuche.
     * Este método se encarga de procesar los archivos cargados y establecer las relaciones correspondientes.
     */
    public function generateRelations(): void
    {
        Log::info("AfipMiSimplificacion->generateRelations");
        $this->fileProcessingService->processFiles();
    }

    public function generateAfipArt():void
    {
        // va a ejecutar la funcion almacenada
    }

    public function render()
    {
        $processLog = ProcessLog::find($this->processLogId);

        return view('livewire.afip-mi-simplificacion', [
            'steps' => $this->steps,
            'currentStep' => $this->currentStep,
            'processLog' => $processLog,
            'processLogId' => $this->processLogId,
            'messages' => $this->messageManager->getMessages(),
        ]);
    }
}
