<?php

namespace App\Services;

use App\Contracts\WorkflowExecutionInterface;
use App\Contracts\WorkflowServiceInterface;
use App\Enums\WorkflowStatus;
use App\Models\AfipMapucheMiSimplificacion;
use App\Traits\MapucheConnectionTrait;
use App\ValueObjects\NroLiqui;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class WorkflowExecutionService implements WorkflowExecutionInterface
{
    use MapucheConnectionTrait;

    public const  string EVENT_WORKFLOW_STEP_COMPLETED = 'workflow.step.completed';


    //constantes
    private const int  PER_PAGE = 10;
    private const string  IN_PROGRESS = 'in_progress';

    private const string  LOG_INIT_POPULATE_TEMP_TABLE = 'iniciar-poblado-tabla-temp: ';
    private const int  DEFAULT_PERIODO_FISCAL = 202312;
    private const string  EVENT_WORKFLOW_COMPLETED = 'workflow-completed';
    private const string  EVENT_SUCCESS_MAPUCHE_MI_SIMPLIFICACION = 'success-mapuche-mi-simplificacion';
    private const string  EVENT_ERROR_MAPUCHE_MI_SIMPLIFICACION = 'error-mapuche-mi-simplificacion';

    // propiedades protegidas
    protected $currentStep;

    protected $cuilsToSearch;

    protected $cuilsNotInAfip;

    protected $cuilsCount;

    /**
     * @var NroLiqui Número de liquidación
     */
    protected NroLiqui $nroLiqui;

    protected $periodoFiscal = self::DEFAULT_PERIODO_FISCAL;

    protected $perPage = self::PER_PAGE;

    protected $cuilsNoInserted = [];

    protected $showCuilsNoEncontrados = false;

    protected $showCuilsTable;

    protected $ShowMiSimplificacion;

    protected $processLog;

    public function __construct(
        private WorkflowServiceInterface $workflowService,
        private CuilCompareService $cuilCompareService,
        private TempTableService $tempTableService,
        private MapucheMiSimplificacionService $mapucheMiSimplificacion,
        private MessageManager $messageManager,
    ) {
        $this->processLog = $this->workflowService->getLatestWorkflow();

        // Inicializar nroLiqui con un valor por defecto
        try {
            $this->nroLiqui = new NroLiqui(1); // Valor por defecto que será reemplazado
        } catch (\InvalidArgumentException $e) {
            Log::error('Error al inicializar NroLiqui: ' . $e->getMessage());
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

            // Dispatch step completed event
            $this->dispatchEvent(self::EVENT_WORKFLOW_STEP_COMPLETED, ['step' => $step->value]);
        }

        $this->dispatchEvent(self::EVENT_WORKFLOW_COMPLETED);
    }

    /**
     * Establece el número de elementos por página.
     */
    public function setPerPage(int $perPage): self
    {
        $this->perPage = $perPage;
        return $this;
    }

    /**
     * Obtiene el número actual de elementos por página.
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Establece el período fiscal.
     */
    public function setPeriodoFiscal(int $periodoFiscal): self
    {
        $this->periodoFiscal = $periodoFiscal;
        return $this;
    }

    /**
     * Obtiene el período fiscal actual.
     */
    public function getPeriodoFiscal(): int
    {
        return $this->periodoFiscal;
    }

    /** Recupera las CUIL (Clave Única de Identificación Laboral) que están presentes en la tabla temporal tabla_temp_cuils pero no en la tabla afip_mapuche_mi_simplificacion.
     *
     * @return array The array of CUILs that are present in the temporary table but not in the afip_mapuche_mi_simplificacion table.
     */
    public function cuilsNoEncontrados(): array
    {
        $model = new AfipMapucheMiSimplificacion();

        return DB::connection($this->getConnectionName())
            ->table($this->tempTableService->getFullTableName() . ' as ttc')
            ->leftJoin($model->getFullTableName() . ' as amms', 'ttc.cuil', 'amms.cuil')
            ->whereNull('amms.cuil')
            ->pluck('ttc.cuil')->toArray();
    }

    /**
     * Obtiene el número de liquidación.
     */
    public function getNroLiqui(): NroLiqui
    {
        return $this->nroLiqui;
    }

    /**
     * Obtiene el valor primitivo del número de liquidación.
     */
    public function getNroLiquiValue(): int
    {
        return $this->nroLiqui->value();
    }

    /**
     * Establece el número de liquidación.
     *
     * @param int|NroLiqui $nroLiqui
     *
     * @throws \InvalidArgumentException Si el valor no es válido
     */
    public function setNroLiqui($nroLiqui): self
    {
        try {
            $this->nroLiqui = ($nroLiqui instanceof NroLiqui) ? $nroLiqui : new NroLiqui($nroLiqui);
            return $this;
        } catch (\InvalidArgumentException $e) {
            Log::error('Error al establecer NroLiqui: ' . $e->getMessage());
            throw $e;
        }
    }

    private function executeStep(\App\Enums\WorkflowStatus $step): void
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
                // no break
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
    private function compareCuils(): Collection
    {
        try {
            $this->cuilsNotInAfip = $this->cuilCompareService->compareCuils($this->perPage);
            return $this->cuilsNotInAfip;
        } catch (QueryException $e) {
            Log::error('Error en la consulta de comparación de CUILs: ' . $e->getMessage());
            throw new \Exception('Error al procesar la comparación de CUILs. Por favor, inténtelo de nuevo más tarde.', $e->getCode(), $e);
        }
    }

    private function poblarTablaTempCuils(): void
    {
        if ($this->currentStep === WorkflowStatus::POBLAR_TABLA_TEMP_CUILS->value) {
            $this->cuilsToSearch = $this->cuilsNotInAfip->toArray();
            $this->workflowService->updateStep($this->processLog, WorkflowStatus::POBLAR_TABLA_TEMP_CUILS->value, self::IN_PROGRESS);
            if ($this->tempTableService->populateTempTable($this->cuilsToSearch)) {
                $this->cuilsCount = $this->tempTableService->getTempTableCount();
                Log::info(self::LOG_INIT_POPULATE_TEMP_TABLE . "{$this->nroLiqui->value()}" . "{$this->periodoFiscal} {$this->cuilsCount}");
            }
        }
    }

    private function ejecutarFuncionAlmacenada(): void
    {
        try {
            $result = $this->mapucheMiSimplificacion->execute($this->nroLiqui, $this->periodoFiscal);
            if ($result) {
                $this->dispatchEvent(self::EVENT_SUCCESS_MAPUCHE_MI_SIMPLIFICACION, ['Función almacenada ejecutada exitosamente']);
            } else {
                $this->dispatchEvent(self::EVENT_ERROR_MAPUCHE_MI_SIMPLIFICACION, ['Error al ejecutar la función almacenada']);
            }
        } catch (\Exception $e) {
            Log::error('Error al ejecutar la función almacenada: ' . $e->getMessage());
            $this->dispatchEvent(self::EVENT_ERROR_MAPUCHE_MI_SIMPLIFICACION, ['Error: ' . $e->getMessage()]);
        }
    }

    /** Carga los CUILs que no están en la tabla afip_relaciones_activas.
     */
    private function loadCuilsNotInserted(): void
    {
        $this->cuilsNoInserted = $this->cuilsNoEncontrados();
        $this->showCuilsNoEncontrados = true;
    }

    private function showParaMiSimplificacionAndCuilsNoEncontrados(): void
    {
        $this->showCuilsTable = false;
        $this->ShowMiSimplificacion = true;

        $this->loadCuilsNotInserted();
    }

    /**
     * Dispatches an event with the given name and payload.
     *
     * @param string $eventName The name of the event to dispatch.
    *  @param array<string, string|int|array<string, mixed>> $payload Event payload data
     * @return void
     */
    private function dispatchEvent(string $eventName, array $payload = []): void
    {
        Event::dispatch($eventName, $payload);
    }
}
