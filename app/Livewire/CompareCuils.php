<?php

namespace App\Livewire;

use App\Contracts\MapucheMiSimplificacionServiceInterface;
use App\Contracts\MessageManagerInterface;
use App\Contracts\WorkflowServiceInterface;
use App\Data\Mapuche\AgenteData;
use App\Enums\WorkflowStatus;
use App\Models\AfipMapucheMiSimplificacion;
use App\Models\Dh01;
use App\Models\Dh03;
use App\Services\CuilCompareService;
use App\Services\ProcessLogService;
use App\Services\TempTableService;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class CompareCuils extends Component
{
    use WithPagination;
    use MapucheConnectionTrait;

    // Constantes
    private const PER_PAGE = 10;
    private const IN_PROGRESS = 'in_progress';
    private const COMPLETED = 'completed';

    private const MSG_WORKFLOW_COMPLETED = 'Flujo de trabajo completado';
    private const MSG_DATA_INSERTED = 'Datos insertados en Mi Simplificacion';
    private const MSG_ERROR_INSERT = 'Error al insertar Mi Simplificacion';
    private const LOG_INIT_POPULATE_TEMP_TABLE = 'iniciar-poblado-tabla-temp: ';

    private const DEFAULT_PERIODO_FISCAL = 202312;
    private const EVENT_WORKFLOW_COMPLETED = 'workflow-completed';
    private const EVENT_SUCCESS_TABLA_TEMP_CUILS = 'success-tabla-temp-cuils';
    private const EVENT_SUCCESS_MAPUCHE_MI_SIMPLIFICACION = 'success-mapuche-mi-simplificacion';
    private const EVENT_ERROR_MAPUCHE_MI_SIMPLIFICACION = 'error-mapuche-mi-simplificacion';

    public $cuilsNotInAfip;

    public $cuilsCount = 0;

    public $nroLiqui = 2;

    public int $periodoFiscal = self::DEFAULT_PERIODO_FISCAL;

    public array $cuilsToSearch = [];

    public array $cuilsNoInserted = [];

    public bool $showCuilsNoEncontrados = false;

    public bool $cuilsNotInAfipLoaded = false;

    public $selectedDni;

    public $employeeInfo;

    public bool $showModal = false;

    public bool $showCargoModal = false;

    public bool $showCreateTempTableButton = false;

    public bool $crearTablaTemp = false;

    public bool $tableTempCreated = false;

    public array $cargos = [];

    public bool $load = false;

    public int $perPage = 10;

    public bool $showDetails = false;

    public string $successMessage = '';

    public bool $showCuilsTable = false;

    public bool $insertTablaTemp = false;

    public bool $miSimButton = false;

    public bool $ShowMiSimplificacion = false;

    protected ?string $currentStep;

    protected $processLog;

    protected $workflowService;

    private $messageManager;

    private $cuilCompareService;

    private $tempTableService;

    private $mapucheMiSimplificacionService;

    private $processLogService;

    public function boot(
        WorkflowServiceInterface $workflowService,
        MessageManagerInterface $messageManager,
        CuilCompareService $cuilCompareService,
        TempTableService $tempTableService,
        MapucheMiSimplificacionServiceInterface $mapucheMiSimplificacionService,
        ProcessLogService $processLogService,
    ): void {
        $this->workflowService = $workflowService;
        $this->messageManager = $messageManager;
        $this->cuilCompareService = $cuilCompareService;
        $this->tempTableService = $tempTableService;
        $this->mapucheMiSimplificacionService = $mapucheMiSimplificacionService;
        $this->perPage = self::PER_PAGE;
        $this->processLogService = $processLogService;
    }

    public function mount(): void
    {
        $this->processLog = $this->workflowService->getLatestWorkflow();
        if ($this->processLog) {
            $this->currentStep = $this->workflowService->getCurrentStep($this->processLog);
            if ($this->currentStep === null) {
                $this->showParaMiSimplificacionAndCuilsNoEncontrados();
                return;
            }
        } else {
            $this->currentStep = WorkflowStatus::OBTENER_CUILS_NOT_IN_AFIP->value;
        }

        $this->handleCurrentState();
    }

    public function showParaMiSimplificacionAndCuilsNoEncontrados(): void
    {
        $this->showCuilsTable = false;
        $this->ShowMiSimplificacion = true;

        $this->loadCuilsNotInserted();
    }

    /** Recupera las CUIL (Clave Única de Identificación Laboral) que están presentes en la tabla temporal tabla_temp_cuils pero no en la tabla afip_mapuche_mi_simplificacion.
     *
     * @return array The array of CUILs that are present in the temporary table but not in the afip_mapuche_mi_simplificacion table.
     */
    public function cuilsNoEncontrados(): array
    {
        $afipModel = new AfipMapucheMiSimplificacion();

        $cuilsNoEncontrados = DB::connection($this->getConnectionName())
            ->table($this->tempTableService->getFullTableName() . ' as ttc')
            ->leftJoin($afipModel->getFullTableName() . ' as amms', 'ttc.cuil', 'amms.cuil')
            ->whereNull('amms.cuil')
            ->pluck('ttc.cuil')
            ->toArray();

        return $cuilsNoEncontrados;
    }

    #[Computed]
    public function messages(): array
    {
        return $this->messageManager->getMessages();
    }

    #[Computed]
    public function isTableTempCreationRequired(): bool
    {
        return $this->currentStep === WorkflowStatus::POBLAR_TABLA_TEMP_CUILS->value || $this->currentStep === WorkflowStatus::EJECUTAR_FUNCION_ALMACENADA->value;
    }

    #[Computed]
    public function shouldShowParaMiSimplificacion(): bool
    {
        return \in_array($this->currentStep, [
            WorkflowStatus::OBTENER_CUILS_NO_INSERTADOS->value,
            WorkflowStatus::EXPORTAR_TXT_PARA_AFIP->value,
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

    #[On(self::EVENT_WORKFLOW_COMPLETED)]
    public function handleWorkflowCompleted(): void
    {
        $this->addMessage(self::MSG_WORKFLOW_COMPLETED, 'success');
        $this->showParaMiSimplificacionAndCuilsNoEncontrados();
    }

    public function updateShowMiSimplificacion(): void
    {
        $this->ShowMiSimplificacion = !$this->ShowMiSimplificacion;
    }

    #[Computed(persist: true)]
    public function stepsCompleted(): bool
    {
        $step = $this->currentStep;
        return (bool)($step === self::COMPLETED);
    }

    /**
     * Ejecuta la función almacenada 'mapuche-mi-simplificacion' y actualiza el paso del flujo de trabajo a self::IN_PROGRESS.
     * Luego, restablece la propiedad 'cuilsNotInAfipLoaded'.
     */
    public function mapucheMiSimplificacion(): void
    {
        $this->workflowService->updateStep($this->processLog, WorkflowStatus::EJECUTAR_FUNCION_ALMACENADA->value, self::IN_PROGRESS);

        $this->dispatch('mapuche-mi-simplificacion', $this->nroLiqui, $this->periodoFiscal);
        $this->reset('cuilsNotInAfipLoaded');
    }

    /** Maneja el éxito de la población de la tabla temporal de CUILs.
     * Completa el paso 'poblar_tabla_temp_cuils' en el registro de flujo de trabajo y actualiza el paso 'ejecutar_funcion_almacenada' a self::IN_PROGRESS.
     * Luego, llama al método 'ejecutarFuncionAlmacenada()' para iniciar el siguiente paso del flujo de trabajo.
     */
    #[On(self::EVENT_SUCCESS_TABLA_TEMP_CUILS)]
    public function handleTablaTempCuilsSuccess(): void
    {
        $this->workflowService->completeStep($this->processLog, WorkflowStatus::POBLAR_TABLA_TEMP_CUILS->value);

        // Iniciar el siguiente paso: ejecutar_funcion_almacenada
        $this->workflowService->updateStep($this->processLog, WorkflowStatus::EJECUTAR_FUNCION_ALMACENADA->value, self::IN_PROGRESS);
        $this->ejecutarFuncionAlmacenada();
    }

    #[On('download-mi-simplificacion')]
    public function handleExportTxtSuccess(): void
    {
        $this->processLog = $this->workflowService->getLatestWorkflow();
        $this->currentStep = WorkflowStatus::EXPORTAR_TXT_PARA_AFIP->value;
        $this->workflowService->completeStep($this->processLog, $this->currentStep);
    }

    public function funcionAlmacenada(): void
    {
        $this->processLog = $this->workflowService->getLatestWorkflow();
        $this->currentStep = $this->workflowService->getCurrentStep($this->processLog);
        if ($this->currentStep === WorkflowStatus::EJECUTAR_FUNCION_ALMACENADA->value) {
            $this->workflowService->updateStep($this->processLog, WorkflowStatus::EJECUTAR_FUNCION_ALMACENADA->value, self::IN_PROGRESS);
            $this->ejecutarFuncionAlmacenada();
            $this->dispatch(self::EVENT_SUCCESS_MAPUCHE_MI_SIMPLIFICACION, $this->currentStep, $this->processLog);
        }
    }

    /** Maneja el éxito de la ejecución de la función "mapuche-mi-simplificacion".
     * Este método se ejecuta cuando se recibe un evento de éxito de la función "mapuche-mi-simplificacion".
     * Actualiza el estado de la aplicación, completa el paso "ejecutar_funcion_almacenada" en el flujo de trabajo,
     * muestra un mensaje de éxito, y verifica si hay CUILs que no se insertaron en la tabla "afip_mapuche_mi_simplificacion".
     * Si hay CUILs no insertados, los guarda en la propiedad "cuilsNoInserted" y muestra un mensaje con esa información.
     * Finalmente, inicia el siguiente paso del flujo de trabajo si existe.
     */
    #[On(self::EVENT_SUCCESS_MAPUCHE_MI_SIMPLIFICACION)]
    public function handleSuccessMapucheMiSimplificacion(): void
    {
        $this->processLog = $this->workflowService->getLatestWorkflow();
        $this->currentStep = $this->workflowService->getCurrentStep($this->processLog);

        $this->workflowService->completeStep($this->processLog, WorkflowStatus::EJECUTAR_FUNCION_ALMACENADA->value);

        $this->successMessage = self::MSG_DATA_INSERTED;
        $this->miSimButton = false;

        $count = AfipMapucheMiSimplificacion::count();
        $this->successMessage = "Datos insertados en Mi Simplificacion: {$count}";

        $result = \count($this->cuilsToSearch) - $count;
        $this->ShowMiSimplificacion = true;

        $nextStep = $this->workflowService->getNextStep(WorkflowStatus::EJECUTAR_FUNCION_ALMACENADA->value);

        if ($nextStep) {
            $this->workflowService->updateStep($this->processLog, $nextStep, self::IN_PROGRESS);
        }
    }

    #[On(self::EVENT_ERROR_MAPUCHE_MI_SIMPLIFICACION)]
    public function handleErrorMapucheMiSimplificacion(): void
    {
        $this->successMessage = self::MSG_ERROR_INSERT;
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
    public function restart(): void
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
     *
     * @return bool El valor alternado.
     */
    public function toggleValue(bool|string $value): bool
    {
        return $value = (bool)$value === false;
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
    public function loadCuilsNotInAfip(): void
    {
        try {
            Log::info('loadCuilsNotInAfip iniciado');

            // Verificar el último registro de ProcessLog
            Log::info('Buscando el último registro de ProcessLog en ProcessLogService');
            $lastProcess = $this->processLogService->getLatestProcess();

            // Obtener el siguiente paso del workflow
            $nextStep = $this->workflowService->getNextStep($lastProcess?->current_step ?? 'start');

            if (!$nextStep) {
                Log::warning('No se pudo determinar el siguiente paso del workflow');
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'No se pudo determinar el siguiente paso del proceso',
                ]);
                return;
            }

            // Obtener la URL del paso actual
            $stepUrl = $this->workflowService->getStepUrl($nextStep);

            if (!$stepUrl) {
                Log::error('No se pudo obtener la URL del paso actual');
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Error al obtener la URL del paso actual',
                ]);
                return;
            }

            // Resto de la lógica...

        } catch (\Exception $e) {
            Log::error('Error en loadCuilsNotInAfip: ' . $e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al cargar los CUILs: ' . $e->getMessage(),
            ]);
        }
    }

    public function executeWorkflowSteps(): void
    {
        $steps = [
            WorkflowStatus::OBTENER_CUILS_NOT_IN_AFIP,
            WorkflowStatus::POBLAR_TABLA_TEMP_CUILS,
            WorkflowStatus::EJECUTAR_FUNCION_ALMACENADA,
            WorkflowStatus::OBTENER_CUILS_NO_INSERTADOS,
            WorkflowStatus::EXPORTAR_TXT_PARA_AFIP,
        ];

        foreach ($steps as $step) {
            $this->executeStep($step);
            $this->workflowService->completeStep($this->processLog, $step->value);
            $this->currentStep = $this->workflowService->getNextStep($step->value);
        }

        $this->dispatch(self::EVENT_WORKFLOW_COMPLETED);
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

    public function poblarTablaTempCuils(): void
    {
        if ($this->currentStep === WorkflowStatus::POBLAR_TABLA_TEMP_CUILS->value) {
            $this->cuilsToSearch = $this->cuilsNotInAfip->toArray();
            $this->workflowService->updateStep($this->processLog, WorkflowStatus::POBLAR_TABLA_TEMP_CUILS->value, self::IN_PROGRESS);
            if ($this->tempTableService->populateTempTable($this->cuilsToSearch)) {
                $this->cuilsCount = $this->tempTableService->getTempTableCount();
                Log::info(self::LOG_INIT_POPULATE_TEMP_TABLE . "{$this->nroLiqui}" . "{$this->periodoFiscal} {$this->cuilsCount}");
            }
        }
    }

    public function searchEmployee($dni): void
    {
        $this->selectedDni = $dni;
        $employee = Dh01::where('nro_docum', $dni)->first();

        if ($employee) {
            $agenteData = AgenteData::fromModel($employee);

            // Mantenemos el mismo formato de array para no romper la vista
            $this->employeeInfo = [
                'nombre' => $agenteData->nombre,
                'apellido' => $agenteData->apellido,
                'nro_legaj' => $agenteData->nroLegaj,
                'DNI' => $agenteData->dni,
                'fecha_inicio' => $agenteData->fechaInicio?->format('Y-m-d'),
            ];
            $this->showModal = true;
        } else {
            $this->employeeInfo = null;
            $this->showModal = true;
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->employeeInfo = null;
        $this->selectedDni = null;
    }

    public function showCargos($nroLegaj): void
    {
        $this->cargos = Dh03::where('nro_legaj', $nroLegaj)
            ->orderBy('fec_alta', 'desc')
            ->get(['nro_cargo', 'codc_categ', 'fec_alta', 'fec_baja', 'vig_caano', 'vig_cames', 'chkstopliq'])
            ->toArray();

        $this->showCargoModal();
        $this->closeShowModal();
    }

    public function closeCargoModal(): void
    {
        $this->showCargoModal = false;
        $this->cargos = [];
    }

    public function showLoadCuilsNoEncontrados(): void
    {
        $this->processLog = $this->workflowService->getLatestWorkflow();
        $this->currentStep = $this->workflowService->getCurrentStep($this->processLog);
        $this->loadCuilsNotInserted();
        $this->workflowService->completeStep($this->processLog, WorkflowStatus::OBTENER_CUILS_NO_INSERTADOS->value);
    }

    public function render()
    {
        return view('livewire.compare-cuils');
    }

    protected function showCargoModal(): void
    {
        $this->showCargoModal = true;
    }

    protected function closeShowModal(): void
    {
        $this->showModal = false;
    }

    /** Carga los CUILs que no están en la tabla afip_relaciones_activas.
     *
     * @return void
     */
    private function loadCuilsNotInserted(): void
    {
        $this->cuilsNoInserted = $this->cuilsNoEncontrados();
        $this->showCuilsNoEncontrados = true;
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

    private function handlePoblarTablaTempCuils(): void
    {
        $this->crearTablaTemp = true;
        $this->cuilsCount = $this->tempTableService->getTempTableCount();
        if ($this->cuilsCount == 0) {
            $this->workflowService->updateStep($this->processLog, WorkflowStatus::OBTENER_CUILS_NOT_IN_AFIP->value, self::IN_PROGRESS);
            $this->addMessage('Volviendo al paso anterior: ' . WorkflowStatus::OBTENER_CUILS_NOT_IN_AFIP->value, 'info');
        }
    }

    private function addMessage(string $message, string $type = 'info'): void
    {
        $this->messageManager->clearMessages();
        $this->messageManager->addMessage($message, $type);
        $this->dispatch('message-added', ['message' => $message, 'type' => $type]);
    }

    private function ejecutarFuncionAlmacenada(): void
    {
        $result = $this->mapucheMiSimplificacionService->execute($this->nroLiqui, $this->periodoFiscal);
        if ($result) {
            $this->dispatch(self::EVENT_SUCCESS_MAPUCHE_MI_SIMPLIFICACION, 'Función almacenada ejecutada exitosamente');
        } else {
            $this->dispatch(self::EVENT_ERROR_MAPUCHE_MI_SIMPLIFICACION, 'Error al ejecutar la función almacenada');
        }
    }

    private function executeStep($step): void
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
                $this->dispatch(self::EVENT_WORKFLOW_COMPLETED);
                // no break
            case WorkflowStatus::EXPORTAR_TXT_PARA_AFIP:
                $this->showParaMiSimplificacionAndCuilsNoEncontrados();
                break;
        }
    }
}
