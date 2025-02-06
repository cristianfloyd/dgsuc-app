<?php

namespace App\Services;

use App\Enums\WorkflowStatus;
use App\Models\TablaTempCuils;
use App\Services\TempTableService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CuilCompareService;
use Illuminate\Support\Facades\Event;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\QueryException;
use App\Contracts\MessageManagerInterface;
use App\Contracts\WorkflowServiceInterface;
use App\Models\AfipMapucheMiSimplificacion;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\WorkflowExecutionInterface;
use App\Contracts\MapucheMiSimplificacionServiceInterface;

class WorkflowExecutionService implements WorkflowExecutionInterface
{
    use MapucheConnectionTrait;


    //constantes
    private const int PER_PAGE = 10;
    private const string IN_PROGRESS = 'in_progress';

    private const string LOG_INIT_POPULATE_TEMP_TABLE = 'iniciar-poblado-tabla-temp: ';

    public const string EVENT_WORKFLOW_STEP_COMPLETED = 'workflow.step.completed';
    private const int DEFAULT_PERIODO_FISCAL = 202312;
    private const string EVENT_WORKFLOW_COMPLETED = 'workflow-completed';
    private const string EVENT_SUCCESS_TABLA_TEMP_CUILS = 'success-tabla-temp-cuils';
    private const string EVENT_SUCCESS_MAPUCHE_MI_SIMPLIFICACION = 'success-mapuche-mi-simplificacion';
    private const string EVENT_ERROR_MAPUCHE_MI_SIMPLIFICACION = 'error-mapuche-mi-simplificacion';

    // propiedades protegidas
    protected $currentStep;
    protected $cuilsToSearch;
    protected $cuilsNotInAfip;
    protected $cuilsCount;
    protected $nroLiqui;
    protected $periodoFiscal = self::DEFAULT_PERIODO_FISCAL;
    protected $perPage = self::PER_PAGE;
    protected $cuilsNoInserted = [];
    protected $showCuilsNoEncontrados = false;
    protected $showCuilsTable;
    protected $ShowMiSimplificacion;

    private WorkflowServiceInterface $workflowService;
    private CuilCompareService $cuilCompareService;
    private TempTableService $tempTableService;
    private MapucheMiSimplificacionService $mapucheMiSimplificacion;
    private MessageManager $messageManager;
    protected $processLog;



    public function __construct(
        WorkflowServiceInterface $workflowService,
        CuilCompareService $cuilCompareService,
        TempTableService $tempTableService,
        MapucheMiSimplificacionServiceInterface $mapucheMiSimplificacion,
        MessageManagerInterface $messageManager,
    ) {
        $this->workflowService = $workflowService;
        $this->cuilCompareService = $cuilCompareService;
        $this->tempTableService = $tempTableService;
        $this->mapucheMiSimplificacion = $mapucheMiSimplificacion;
        $this->messageManager = $messageManager;
        $this->processLog = $this->workflowService->getLatestWorkflow();
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

            // Dispatch step completed event
            $this->dispatchEvent(self::EVENT_WORKFLOW_STEP_COMPLETED, ['step' => $step->value]);
        }

        $this->dispatchEvent(self::EVENT_WORKFLOW_COMPLETED);
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
                $this->dispatchEvent(self::EVENT_WORKFLOW_COMPLETED);
            case WorkflowStatus::EXPORTAR_TXT_PARA_AFIP:
                $this->showParaMiSimplificacionAndCuilsNoEncontrados();
                break;
        }
    }

    /**
     * Establece el número de elementos por página.
     *
     * @param int $perPage
     * @return self
     */
    public function setPerPage(int $perPage): self
    {
        $this->perPage = $perPage;
        return $this;
    }

    /**
     * Obtiene el número actual de elementos por página.
     *
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Establece el período fiscal.
     *
     * @param int $periodoFiscal
     * @return self
     */
    public function setPeriodoFiscal(int $periodoFiscal): self
    {
        $this->periodoFiscal = $periodoFiscal;
        return $this;
    }

    /**
     * Obtiene el período fiscal actual.
     *
     * @return int
     */
    public function getPeriodoFiscal(): int
    {
        return $this->periodoFiscal;
    }


    /** Compara las CUIL (Clave Única de Identificación Laboral) del modelo AfipMapucheSicoss con las CUIL del modelo AfipRelacionesActivas.
     *
     * Este método recupera todos los CUIL del modelo AfipRelacionesActivas, y luego encuentra todos los CUIL del modelo AfipMapucheSicoss
     * que no están presentes en el modelo AfipRelacionesActivas.
     * Los CUIL resultantes que no están en el modelo AfipRelacionesActivas se almacenan en la propiedad $cuilsNotInAfip.
     */
    private function compareCuils(): Collection
    {
        try {
            $this->cuilsNotInAfip = $this->cuilCompareService->compareCuils($this->perPage);
            return $this->cuilsNotInAfip;
        } catch (QueryException $e) {
            Log::error('Error en la consulta de comparación de CUILs: ' . $e->getMessage());
            throw new \Exception('Error al procesar la comparación de CUILs. Por favor, inténtelo de nuevo más tarde.');
        }
    }

    private function poblarTablaTempCuils(): void
    {
        if ($this->currentStep === WorkflowStatus::POBLAR_TABLA_TEMP_CUILS->value) {
            $this->cuilsToSearch = $this->cuilsNotInAfip->toArray();
            $this->workflowService->updateStep($this->processLog, WorkflowStatus::POBLAR_TABLA_TEMP_CUILS->value, self::IN_PROGRESS);
            if ($this->tempTableService->populateTempTable($this->cuilsToSearch)) {
                $this->cuilsCount = $this->tempTableService->getTempTableCount();
                Log::info(self::LOG_INIT_POPULATE_TEMP_TABLE . "{$this->nroLiqui}" . "{$this->periodoFiscal} {$this->cuilsCount}");
            };
        }
    }

    private function ejecutarFuncionAlmacenada(): void
    {
        $result = $this->mapucheMiSimplificacion->execute($this->nroLiqui, $this->periodoFiscal);
        if ($result) {
            $this->dispatchEvent(self::EVENT_SUCCESS_MAPUCHE_MI_SIMPLIFICACION, ['Función almacenada ejecutada exitosamente']);
        } else {
            $this->dispatchEvent(self::EVENT_ERROR_MAPUCHE_MI_SIMPLIFICACION, ['Error al ejecutar la función almacenada']);
        }
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
    private function loadCuilsNotInAfip()
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

    /** Carga los CUILs que no están en la tabla afip_relaciones_activas
     *
     * @return void
     */
    private function loadCuilsNotInserted(): void
    {
        $this->cuilsNoInserted = $this->cuilsNoEncontrados();
        $this->showCuilsNoEncontrados = true;
    }

    /** Recupera las CUIL (Clave Única de Identificación Laboral) que están presentes en la tabla temporal tabla_temp_cuils pero no en la tabla afip_mapuche_mi_simplificacion.
     *
     * @return array The array of CUILs that are present in the temporary table but not in the afip_mapuche_mi_simplificacion table.
     */
    public function cuilsNoEncontrados(): array
    {
        $model = new AfipMapucheMiSimplificacion();
        $cuilsNoEncontrados = DB::connection($this->getConnectionName())
        ->table($this->tempTableService->getFullTableName() . ' as ttc')
        ->leftJoin($model->getFullTableName(). ' as amms', 'ttc.cuil', 'amms.cuil')
        ->whereNull('amms.cuil')
        ->pluck('ttc.cuil')->toArray();

        return $cuilsNoEncontrados;
    }

    private function showParaMiSimplificacionAndCuilsNoEncontrados(): void
    {
        $this->showCuilsTable = false;
        $this->ShowMiSimplificacion = true;

        $this->loadCuilsNotInserted();
    }

    // New method to dispatch events
    private function dispatchEvent(string $eventName, array $payload = []): void
    {
        Event::dispatch($eventName, $payload);
    }

    /**
     * Get the value of nroLiqui
     */
    public function getNroLiqui(): int
    {
        return $this->nroLiqui;
    }

    /**
     * Set the value of nroLiqui
     *
     * @return  self
     */
    public function setNroLiqui($nroLiqui): self
    {
        $this->nroLiqui = $nroLiqui;

        return $this;
    }
}
