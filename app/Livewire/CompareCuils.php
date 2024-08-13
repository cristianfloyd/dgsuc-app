<?php

namespace App\Livewire;

use App\Models\Dh01;
use App\Models\Dh03;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Enum\WorkflowStatus;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Services\TempTableService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CuilCompareService;
use Illuminate\Database\QueryException;
use App\Contracts\MessageManagerInterface;
use App\Contracts\WorkflowServiceInterface;
use App\Models\AfipMapucheMiSimplificacion;
use App\Contracts\MapucheMiSimplificacionServiceInterface;

class CompareCuils extends Component
{
    use WithPagination;

    private const int PER_PAGE = 10;
    public $cuilsNotInAfip;
    public $cuilsCount = 0;
    public $nroLiqui = 1;
    public int $periodoFiscal = 202312;
    public $cuilsToSearch = [];

    public $cuilsNoInserted = [];
    public $showCuilsNoEncontrados = false;
    public $cuilsNotInAfipLoaded = false;
    public $selectedDni;
    public $employeeInfo;
    public $showModal = false;
    public $showCargoModal = false;
    public $showCreateTempTableButton = false;
    public $crearTablaTemp = false;
    public $tableTempCreated = false;
    public $cargos = [];
    public $load = false;
    public $perPage = 10;
    public $showDetails = false;
    public $successMessage = '';
    public $showCuilsTable = false;
    public $insertTablaTemp = false;
    public $miSimButton = false;
    public $ShowMiSimplificacion = false;


    protected ?string $currentStep;
    protected $processLog;
    protected $workflowService;
    private $messageManager;
    private $cuilCompareService;
    private $tempTableService;
    private $mapucheMiSimplificacionService;

    public function boot(WorkflowServiceInterface $workflowService, MessageManagerInterface $messageManager, CuilCompareService $cuilCompareService, TempTableService $tempTableService, MapucheMiSimplificacionServiceInterface $mapucheMiSimplificacionService)
    {
        $this->workflowService = $workflowService;
        $this->messageManager = $messageManager;
        $this->cuilCompareService = $cuilCompareService;
        $this->tempTableService = $tempTableService;
        $this->mapucheMiSimplificacionService = $mapucheMiSimplificacionService;
        $this->perPage = self::PER_PAGE;
    }

    public function mount()
    {
        $this->processLog = $this->workflowService->getLatestWorkflow();
        if ($this->processLog) {
            $this->currentStep = $this->workflowService->getCurrentStep($this->processLog);
            if($this->currentStep === null)
            {
                $this->showParaMiSimplificacionAndCuilsNoEncontrados();
                return;
            };
        } else {
            $this->currentStep = WorkflowStatus::OBTENER_CUILS_NOT_IN_AFIP->value;
        }

        $this->handleCurrentState();
    }

    /**
     * Maneja el estado actual del flujo de trabajo y realiza las acciones correspondientes.
     *
     * Dependiendo del estado actual del flujo de trabajo, este método llama a los métodos
     * apropiados para manejar cada estado.
     */
    private function handleCurrentState(): void
    {
        switch (WorkflowStatus::from($this->currentStep)) {
            case WorkflowStatus::POBLAR_TABLA_TEMP_CUILS:
                $this->handlePoblarTablaTempCuils();
                break;
            case WorkflowStatus::EJECUTAR_FUNCION_ALMACENADA:
                $this->crearTablaTemp = true;
                break;
            case WorkflowStatus::OBTENER_CUILS_NO_INSERTADOS:
            case WorkflowStatus::EXPORTAR_TXT_PARA_AFIP:
                $this->showParaMiSimplificacionAndCuilsNoEncontrados();
                break;
        }
    }

    private function handlePoblarTablaTempCuils()
    {
        $this->crearTablaTemp = true;
        $this->cuilsCount = $this->tempTableService->getTempTableCount();
        if ($this->cuilsCount == 0) {
            $this->workflowService->updateStep($this->processLog, WorkflowStatus::OBTENER_CUILS_NOT_IN_AFIP->value, 'in_progress');
            $this->addMessage('Volviendo al paso anterior: ' . WorkflowStatus::OBTENER_CUILS_NOT_IN_AFIP->value, 'info');
        }
    }

    private function addMessage(string $message, string $type = 'info'): void
    {
        $this->messageManager->clearMessages();
        $this->messageManager->addMessage($message, $type);
        $this->dispatch('message-added', ['message' => $message, 'type' => $type]);
    }


    #[Computed]
    public function messages(): array
    {
        return $this->messageManager->getMessages();
    }
    #[Computed]
    public function isTableTempCreationRequired(): bool
    {
        return $this->currentStep === 'poblar_tabla_temp_cuils' || $this->currentStep === 'ejecutar_funcion_almacenada';
    }


    #[Computed]
    public function shouldShowParaMiSimplificacion(): bool
    {
        return in_array($this->currentStep, [
            WorkflowStatus::OBTENER_CUILS_NO_INSERTADOS->value,
            WorkflowStatus::EXPORTAR_TXT_PARA_AFIP->value
        ]);
    }

    #[Computed]
    public function isProcessStarted(): bool
    {
        return $this->processLog !== null;
    }

    #[Computed]
    public function showLoadButton(): bool
    {
        return $this->currentStep === WorkflowStatus::OBTENER_CUILS_NOT_IN_AFIP->value;
    }

    #[Computed]
    public function showCuilsNoInsertedButton(): bool
    {
        return $this->currentStep === WorkflowStatus::OBTENER_CUILS_NO_INSERTADOS->value;
    }

    #[Computed]
    public function showExecuteStoredFunctionButton(): bool
    {
        return $this->currentStep === WorkflowStatus::EJECUTAR_FUNCION_ALMACENADA->value;
    }

    #[On('workflow-completed')]
    public function handleWorkflowCompleted(): void
    {
        $this->addMessage('Flujo de trabajo completado', 'success');
        $this->showParaMiSimplificacionAndCuilsNoEncontrados();
    }




    public function showParaMiSimplificacionAndCuilsNoEncontrados(): void
    {
        $this->showCuilsTable = false;
        $this->ShowMiSimplificacion = true;

        $this->loadCuilsNotInserted();

    }
    public function updateShowMiSimplificacion()
    {
        $this->ShowMiSimplificacion = !$this->ShowMiSimplificacion;
    }
    #[Computed(persist: true)]
    public function stepsCompleted(): bool
    {
        $step = $this->currentStep;
        if ($step === 'completed') {
            return true;
        }
        return false;
    }


    /**
     * Ejecuta la función almacenada 'mapuche-mi-simplificacion' y actualiza el paso del flujo de trabajo a 'in_progress'.
     * Luego, restablece la propiedad 'cuilsNotInAfipLoaded'.
     */
    public function mapucheMiSimplificacion(): void
    {
        $this->workflowService->updateStep($this->processLog, 'ejecutar_funcion_almacenada', 'in_progress');

        $this->dispatch('mapuche-mi-simplificacion', $this->nroLiqui, $this->periodoFiscal);
        $this->reset('cuilsNotInAfipLoaded');
    }

    public function poblarTablaTempCuils(): void
    {
        if ($this->currentStep === WorkflowStatus::POBLAR_TABLA_TEMP_CUILS->value)
        {
            $this->cuilsToSearch = $this->cuilsNotInAfip->toArray();
            $this->workflowService->updateStep($this->processLog, 'poblar_tabla_temp_cuils', 'in_progress');
            if($this->tempTableService->populateTempTable($this->cuilsToSearch))
            {
                $this->cuilsCount = $this->tempTableService->getTempTableCount();
                Log::info("iniciar-poblado-tabla-temp: {$this->nroLiqui} . {$this->periodoFiscal} {$this->cuilsCount}");
            };

        }
    }

    /** Maneja el éxito de la población de la tabla temporal de CUILs.
     * Completa el paso 'poblar_tabla_temp_cuils' en el registro de flujo de trabajo y actualiza el paso 'ejecutar_funcion_almacenada' a 'in_progress'.
     * Luego, llama al método 'ejecutarFuncionAlmacenada()' para iniciar el siguiente paso del flujo de trabajo.
     */
    #[On('success-tabla-temp-cuils')]
    public function handleTablaTempCuilsSuccess()
    {
        $this->workflowService->completeStep($this->processLog, 'poblar_tabla_temp_cuils');

        // Iniciar el siguiente paso: ejecutar_funcion_almacenada
        $this->workflowService->updateStep($this->processLog, 'ejecutar_funcion_almacenada', 'in_progress');
        $this->ejecutarFuncionAlmacenada();
    }

    #[On('download-mi-simplificacion')]
    public function handleExportTxtSuccess()
    {
        $this->processLog = $this->workflowService->getLatestWorkflow();
        $this->currentStep = WorkflowStatus::EXPORTAR_TXT_PARA_AFIP->value;
        $this->workflowService->completeStep($this->processLog, $this->currentStep);

    }
    public function funcionAlmacenada()
    {
        $this->processLog = $this->workflowService->getLatestWorkflow();
        $this->currentStep = $this->workflowService->getCurrentStep($this->processLog);
        if ($this->currentStep === WorkflowStatus::EJECUTAR_FUNCION_ALMACENADA->value){
            $this->workflowService->updateStep($this->processLog, 'ejecutar_funcion_almacenada', 'in_progress');
            $this->ejecutarFuncionAlmacenada();
            $this->dispatch('success-mapuche-mi-simplificacion', $this->currentStep, $this->processLog);
        }
    }

    private function ejecutarFuncionAlmacenada(): void
    {
        $result = $this->mapucheMiSimplificacionService->execute($this->nroLiqui, $this->periodoFiscal);
        if ($result) {
            $this->dispatch('success-mapuche-mi-simplificacion', 'Función almacenada ejecutada exitosamente');
        } else {
            $this->dispatch('error-mapuche-mi-simplificacion', 'Error al ejecutar la función almacenada');
        }
    }

    /** Recupera las CUIL (Clave Única de Identificación Laboral) que están presentes en la tabla temporal tabla_temp_cuils pero no en la tabla afip_mapuche_mi_simplificacion.
     *
     * @return array The array of CUILs that are present in the temporary table but not in the afip_mapuche_mi_simplificacion table.
     */
    public function cuilsNoEncontrados(): array
    {
        $cuilsNoEncontrados = DB::connection('pgsql-mapuche')->table('suc.tabla_temp_cuils as ttc')->leftJoin('suc.afip_mapuche_mi_simplificacion as amms', 'ttc.cuil', 'amms.cuil')->whereNull('amms.cuil')->pluck('ttc.cuil')->toArray();

        return $cuilsNoEncontrados;
    }

    /** Maneja el éxito de la ejecución de la función "mapuche-mi-simplificacion".
     * Este método se ejecuta cuando se recibe un evento de éxito de la función "mapuche-mi-simplificacion".
     * Actualiza el estado de la aplicación, completa el paso "ejecutar_funcion_almacenada" en el flujo de trabajo,
     * muestra un mensaje de éxito, y verifica si hay CUILs que no se insertaron en la tabla "afip_mapuche_mi_simplificacion".
     * Si hay CUILs no insertados, los guarda en la propiedad "cuilsNoInserted" y muestra un mensaje con esa información.
     * Finalmente, inicia el siguiente paso del flujo de trabajo si existe.
     */
    #[On('success-mapuche-mi-simplificacion')]
    public function handleSuccessMapucheMiSimplificacion(): void
    {
        $this->processLog = $this->workflowService->getLatestWorkflow();
        $this->currentStep = $this->workflowService->getCurrentStep($this->processLog);

        $this->workflowService->completeStep($this->processLog, 'ejecutar_funcion_almacenada');

        $this->successMessage = 'Datos insertados en Mi Simplificacion';
        $this->miSimButton = false;

        $count = AfipMapucheMiSimplificacion::count();
        $this->successMessage = "Datos insertados en Mi Simplificacion: {$count}";

        $result = count($this->cuilsToSearch) - $count;
        $this->ShowMiSimplificacion = true;

        $nextStep = $this->workflowService->getNextStep('ejecutar_funcion_almacenada');

        if ($nextStep) {
            $this->workflowService->updateStep($this->processLog, $nextStep, 'in_progress');
        }
    }



    #[On('error-mapuche-mi-simplificacion')]
    public function handleErrorMapucheMiSimplificacion()
    {
        $this->successMessage = 'Error al insertar Mi Simplificacion';
        $this->restart();
    }

    /** Reinicia varias propiedades de la clase Livewire.
     *
     * Este método restablece las siguientes propiedades:
     * - `cuilsNotInAfipLoaded`
     * - `showCuilsTable`
     * - `showDetails`
     * - `crearTablaTemp`
     * - `insertTablaTemp`
     * - `miSimButton`
     *
     * Esto se utiliza para limpiar el estado de la clase y preparar para una nueva ejecución.
     */
    public function restart()
    {
        $this->reset('cuilsNotInAfipLoaded');
        $this->reset('showCuilsTable');
        $this->reset('showDetails');
        $this->reset('crearTablaTemp');
        $this->reset('insertTablaTemp');
        $this->reset('miSimButton');
    }

    /** Alterna el valor booleano de una variable.
     *
     * @param bool|string $value El valor a alternar.
     * @return bool El valor alternado.
     */
    public function toggleValue(bool|string $value): bool
    {
        return $value = (bool) $value === false;
    }

    /** Carga los CUILs que no se encuentran en AFIP.
     *
     * Este método se encarga de cargar los CUILs que no se encuentran en la tabla AFIP_RELACIONES_ACTIVAS.
     * Primero verifica si existe un registro de flujo de trabajo, y si no, lo inicia. Luego, obtiene el paso actual
     * del flujo de trabajo. Si el paso actual es "obtener_cuils_not_in_afip", entonces se ejecuta la lógica
     * para cargar los CUILs no encontrados en AFIP, se marca el paso como completado y se actualiza el estado
     * de algunas propiedades de la clase. Si el paso actual no es el correcto, se obtiene la URL del paso
     * correcto y se redirige al usuario.
     */
    public function loadCuilsNotInAfip()
    {
        Log::info('loadCuilsNotInAfip iniciado');
        $this->processLog = $this->workflowService->getLatestWorkflow();
        $this->currentStep = $this->workflowService->getCurrentStep($this->processLog);

        if ($this->currentStep === WorkflowStatus::OBTENER_CUILS_NOT_IN_AFIP->value) {
            $this->executeWorkflowSteps();
        } else {
            // Estamos en el paso incorrecto, obtener la url y redireccionar
            $url = $this->workflowService->getStepUrl($this->currentStep);
            Log::warning("url: {$url}");
        }
    }

    public function executeWorkflowSteps()
    {
        $steps = [
            WorkflowStatus::OBTENER_CUILS_NOT_IN_AFIP,
            WorkflowStatus::POBLAR_TABLA_TEMP_CUILS,
            WorkflowStatus::EJECUTAR_FUNCION_ALMACENADA,
            WorkflowStatus::OBTENER_CUILS_NO_INSERTADOS,
            WorkflowStatus::EXPORTAR_TXT_PARA_AFIP
        ];

        foreach ($steps as $step) {
            $this->executeStep($step);
            $this->workflowService->completeStep($this->processLog, $step->value);
            $this->currentStep = $this->workflowService->getNextStep($step->value);
        }

        $this->dispatch('workflow-completed');
    }

    private function executeStep($step)
    {
        switch ($step) {
            case WorkflowStatus::OBTENER_CUILS_NOT_IN_AFIP:
                $this->compareCuils();
                $this->cuilsToSearch = $this->cuilsNotInAfip->toArray();
                break;
            case WorkflowStatus::POBLAR_TABLA_TEMP_CUILS:
                $this->poblarTablaTempCuils();
                break;
            case WorkflowStatus::EJECUTAR_FUNCION_ALMACENADA:
                $this->ejecutarFuncionAlmacenada();
                break;
            case WorkflowStatus::OBTENER_CUILS_NO_INSERTADOS:
                $this->loadCuilsNotInserted();
                $this->dispatch('workflow-completed');
            case WorkflowStatus::EXPORTAR_TXT_PARA_AFIP:
                $this->showParaMiSimplificacionAndCuilsNoEncontrados();
                break;
        }
    }

    /** Compara las CUIL (Clave Única de Identificación Laboral) del modelo AfipMapucheSicoss con las CUIL del modelo AfipRelacionesActivas.
     *
     * Este método recupera todos los CUIL del modelo AfipRelacionesActivas, y luego encuentra todos los CUIL del modelo AfipMapucheSicoss
     * que no están presentes en el modelo AfipRelacionesActivas.
     * Los CUIL resultantes que no están en el modelo AfipRelacionesActivas se almacenan en la propiedad $cuilsNotInAfip.
     */
    public function compareCuils(): Collection
    {
        try {
            $this->cuilsNotInAfip = $this->cuilCompareService->compareCuils($this->perPage);
            return $this->cuilsNotInAfip;
        } catch (QueryException $e) {
            Log::error('Error en la consulta de comparación de CUILs: ' . $e->getMessage());
            throw new \Exception('Error al procesar la comparación de CUILs. Por favor, inténtelo de nuevo más tarde.');
        }
    }



    public function searchEmployee($dni)
    {
        $this->selectedDni = $dni;
        $employee = Dh01::where('nro_docum', $dni)->first();

        if ($employee) {
            $this->employeeInfo = [
                'nombre' => $employee->desc_nombr,
                'apellido' => $employee->desc_appat . ' ' . $employee->desc_apmat,
                'nro_legaj' => $employee->nro_legaj,
                'DNI' => $employee->nro_docum,
                'fecha_inicio' => $employee->dh03()->orderBy('fec_alta', 'asc')->value('fec_alta'),
            ];
            $this->showModal = true;
        } else {
            $this->employeeInfo = null;
            $this->showModal = true;
        }
    }
    public function closeModal()
    {
        $this->showModal = false;
        $this->employeeInfo = null;
        $this->selectedDni = null;
    }

    public function showCargos($nroLegaj)
    {
        $this->cargos = Dh03::where('nro_legaj', $nroLegaj)
            ->orderBy('fec_alta', 'desc')
            ->get(['nro_cargo', 'codc_categ', 'fec_alta', 'fec_baja', 'vig_caano', 'vig_cames', 'chkstopliq'])
            ->toArray();

        $this->showCargoModal();
        $this->closeShowModal();
    }

    protected function showCargoModal()
    {
        $this->showCargoModal = true;
    }
    protected function closeShowModal()
    {
        $this->showModal = false;
    }

    public function closeCargoModal()
    {
        $this->showCargoModal = false;
        $this->cargos = [];
    }

    public function showLoadCuilsNoEncontrados()
    {
        $this->processLog = $this->workflowService->getLatestWorkflow();
        $this->currentStep = $this->workflowService->getCurrentStep($this->processLog);
        $this->loadCuilsNotInserted();
        $this->workflowService->completeStep($this->processLog, WorkflowStatus::OBTENER_CUILS_NO_INSERTADOS->value);
    }

    /** Carga los CUILs que no están en la tabla afip_relaciones_activas
     *
     * @return void
     */
    private function loadCuilsNotInserted()
    {
        $this->cuilsNoInserted = $this->cuilsNoEncontrados();
        $this->showCuilsNoEncontrados = true;
    }



    public function render()
    {
        return view('livewire.compare-cuils');
    }
}
