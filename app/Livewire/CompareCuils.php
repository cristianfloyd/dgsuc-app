<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\TablaTempCuils;
use App\Services\EmployeeService;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Contracts\CuilOperationStrategy;
use App\Strategies\CompareCuilsStrategy;
use App\Contracts\CuilRepositoryInterface;
use App\Contracts\WorkflowServiceInterface;
use App\Models\AfipMapucheMiSimplificacion;
use App\Strategies\LoadCuilsNotInAfipStrategy;
use Illuminate\Pagination\LengthAwarePaginator;

class CompareCuils extends Component
{
    use WithPagination;

    private const STEP_OBTENER_CUILS_NOT_IN_AFIP = 'obtener_cuils_not_in_afip';
    private const STEP_OBTENER_CUILS_NO_INSERTADOS = 'obtener_cuils_no_insertados';
    private const STEP_EJECUTAR_FUNCION_ALMACENADA = 'ejecutar_funcion_almacenada';
    private const STEP_POBLAR_TABLA_TEMP = 'poblar_tabla_temp_cuils';
    private const STEP_EXPORTAR_TXT_PARA_AFIP = 'exportar_txt_para_afip';
    private const DEFAULT_NRO_LIQUI = 3;
    private const DEFAULT_PERIODO_FISCAL = 202312;
    private const STEP_CREAR_TABLA_TEMP = 'crear_tabla_temp';
    private const STEP_INSERTAR_TABLA_TEMP = 'insertar_tabla_temp';
    private const STEP_MOSTRAR_MI_SIMPLIFICACION = 'mostrar_mi_simplificacion';
    private const STEP_SUBIR_ARCHIVO = 'subir_archivo';

    private const PER_PAGE = 10;
    public $cuilsNotInAfip = [];
    public $cuilsCount = 0;
    public $nroLiqui = 3;
    public $periodoFiscal = 202312;
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
    public string $nextStepUrl = '';
    public bool $showUploadForm = false;
    protected $currentStep;
    protected $processLog;

    private $cuilRepository;
    private $currentStrategy;
    private $workflowService;
    private $employeeService;


    public function boot(
        WorkflowServiceInterface $workflowService,
        EmployeeService $employeeService,
        CuilRepositoryInterface $cuilRepository)
    {
        $this->cuilRepository = $cuilRepository;
        $this->workflowService = $workflowService;
        $this->employeeService = $employeeService;
        $this->perPage = self::PER_PAGE;
        $this->processLog = $this->workflowService->getLatestWorkflow();
        $this->checkCurrentStep();
        $strategy = config('cuil_operation_strategy', 'load_cuils_not_in_afip');

        if ($strategy === 'compare_cuils') {
            $this->setStrategy(
                new CompareCuilsStrategy($cuilRepository, self::PER_PAGE));
        } elseif ($strategy === 'load_cuils_not_in_afip') {
            $this->setStrategy(
                new LoadCuilsNotInAfipStrategy($cuilRepository, $workflowService, $this->processLog));
        } else {
            // Estrategia por defecto
            $this->setStrategy(
                new CompareCuilsStrategy($cuilRepository, self::PER_PAGE));
        }
    }

    /**
     * Establece la estrategia actual para la operación de comparación de CUIL.
     *
     * @param CuilOperationStrategy $strategy La estrategia de operación de CUIL a establecer.
     * @return void
     */
    public function setStrategy(CuilOperationStrategy $strategy)
    {
        $this->currentStrategy = $strategy;
    }

    /**
     * Ejecuta la operación de comparación de CUIL utilizando la estrategia de operación actual.
     *
     * @return mixed El resultado de la ejecución de la estrategia de operación actual.
     */
    public function executeOperation()
    {
        return $this->currentStrategy->execute();
    }

    public function mount()
    {
        if ($this->processLog) {
            $this->currentStep = $this->workflowService->getCurrentStep($this->processLog);
            Log::info("mount: currentStep: {$this->currentStep}");

            if ($this->currentStep === self::STEP_POBLAR_TABLA_TEMP) {
                $this->crearTablaTemp = true;
                $this->cuilsCount = TablaTempCuils::count();
                if ($this->cuilsCount == 0) {
                    // volver al paso anterior
                    $this->workflowService->updateStep($this->processLog, self::STEP_OBTENER_CUILS_NOT_IN_AFIP, 'in_pprogress');
                    log::info("mount: volver al paso anterior");
                }
            } else if ($this->currentStep === self::STEP_EJECUTAR_FUNCION_ALMACENADA) {
                $this->crearTablaTemp = true;
            } else if ($this->currentStep === self::STEP_OBTENER_CUILS_NO_INSERTADOS ) {
                $this->showParaMiSimplificacionAndCuilsNoEncontrados();
            } else if ($this->currentStep === self::STEP_EXPORTAR_TXT_PARA_AFIP) {
                $this->showParaMiSimplificacionAndCuilsNoEncontrados();
            } else {
                //metodo para mostrar tabla de cuils no encontrados y componente paraMiSimplificacion
            }
        } else {
            $this->currentStep = null;
        }
        Log::info("mount: currentStep: {$this->currentStep}", ['processLog' => $this->processLog]);
    }

    /** Verifica el paso actual del flujo de trabajo y actualiza el estado de la interfaz de usuario en consecuencia.
     *
     *Este método se encarga de:
     * Obtener el registro de flujo de trabajo más reciente.
     * Obtener el paso actual del flujo de trabajo.
     * Establecer si se debe mostrar el formulario de carga en función del paso actual.
     * Obtener la URL del siguiente paso del flujo de trabajo.
     *
     *@return void
     **/
    public function checkCurrentStep(): void
    {
        $this->currentStep = $this->workflowService->getCurrentStep($this->processLog);
        $this->showUploadForm = in_array($this->currentStep, [self::STEP_POBLAR_TABLA_TEMP, self::STEP_EJECUTAR_FUNCION_ALMACENADA]);
        $this->nextStepUrl = $this->workflowService->getStepUrl($this->currentStep);
    }
    #[Computed]
    public function showLoadButton(): bool
    {
        return $this->currentStep === self::STEP_OBTENER_CUILS_NOT_IN_AFIP;
    }

    #[Computed]
    public function showCuilsNoInsertedButton()
    {
        return $this->currentStep === self::STEP_OBTENER_CUILS_NO_INSERTADOS;
    }

    #[Computed]
    public function showExecuteStoredFunctionButton()
    {
        return $this->currentStep === self::STEP_EJECUTAR_FUNCION_ALMACENADA;
    }


    /**
     * Completa el paso actual del flujo de trabajo y actualiza el registro de flujo de trabajo.
     *
     * Este método se encarga de:
     * - Obtener el paso actual del flujo de trabajo.
     * - Marcar el paso actual como completado en el registro de flujo de trabajo.
     *
     * @return void
     */
    public function completeStep()
    {
        $step = $this->currentStep;

        Log::info("completeStep: currentStep: {$step}");
        $this->workflowService->completeStep($this->processLog, $step);
    }

    /** Metodo para poblar $cuilsNoInserted con los CUILs no encontrados
     * @return void
     */
    public function showParaMiSimplificacionAndCuilsNoEncontrados(): void
    {
        $this->showCuilsTable = false;
        $this->ShowMiSimplificacion = true;
        $this->loadCuilsNotInserted();

        // Asegúrate de que $cuilsNoInserted esté poblado con los CUILs no encontrados
        if (empty($this->cuilsNoInserted)) {
            // Aquí puedes agregar lógica para poblar $cuilsNoInserted si es necesario
        }
    }

    #[Computed( persist: true)]
    public function stepsCompleted(): bool
    {
        $step = $this->currentStep;
        if ($step === 'completed') {
            return true;
        }
        return false;
    }

    /** Ejecuta la lógica de "mapuche-mi-simplificacion" y actualiza el paso "ejecutar_funcion_almacenada" en el registro de flujo de trabajo a "in_progress".
     * También restablece la propiedad "cuilsNotInAfipLoaded".
     */
    public function mapucheMiSimplificacion()
    {
        $this->workflowService->updateStep($this->processLog, self::STEP_EJECUTAR_FUNCION_ALMACENADA, 'in_progress');

        $this->dispatch('mapuche-mi-simplificacion', $this->nroLiqui, $this->periodoFiscal); // Llamada al método del componente TablaTempCuils
        $this->reset('cuilsNotInAfipLoaded');
    }

    public function showCuilsDetails(): void
    {
        $currentStep = $this->currentStep;

        Log::info("showCuilsDetails currentStep: {$currentStep} | processLog: {$this->processLog->id}");

        if ($currentStep === self::STEP_POBLAR_TABLA_TEMP) {
            $this->workflowService->updateStep($this->processLog, self::STEP_POBLAR_TABLA_TEMP, 'in_progress');
            $this->dispatch('iniciar-poblado-tabla-temp', $this->nroLiqui, $this->periodoFiscal, $this->cuilsToSearch);  // Llamada al método del componente TablaTempCuils
            Log::info("showCuilsDetails: iniciar-poblado-tabla-temp");
        }
    }


    /** Maneja el éxito de la población de la tabla temporal de CUILs.
     * Completa el paso self::STEP_POBLAR_TABLA_TEMP en el registro de flujo de trabajo y actualiza el paso self::STEP_EJECUTAR_FUNCION_ALMACENADA a 'in_progress'.
     * Luego, llama al método 'ejecutarFuncionAlmacenada()' para iniciar el siguiente paso del flujo de trabajo.
     */
    #[On('success-tabla-temp-cuils')]
    public function handleTablaTempCuilsSuccess()
    {
        $this->workflowService->completeStep($this->processLog, self::STEP_POBLAR_TABLA_TEMP);

        // Iniciar el siguiente paso: ejecutar_funcion_almacenada
        $this->workflowService->updateStep($this->processLog, self::STEP_EJECUTAR_FUNCION_ALMACENADA, 'in_progress');
        $this->ejecutarFuncionAlmacenada();
    }

    private function ejecutarFuncionAlmacenada()
    {
        // Aquí implementaremos la lógica para ejecutar la función almacenada
        // Por ahora, solo registraremos un mensaje de log
        Log::info('Iniciando ejecución de función almacenada');
        // TODO: Implementar la lógica real para ejecutar la función almacenada
    }


    /** Maneja el éxito de la ejecución de la función "mapuche-mi-simplificacion".
     * Este método se ejecuta cuando se recibe un evento de éxito de la función "mapuche-mi-simplificacion".
     * Actualiza el estado de la aplicación, completa el paso "ejecutar_funcion_almacenada" en el flujo de trabajo,
     * muestra un mensaje de éxito, y verifica si hay CUILs que no se insertaron en la tabla "afip_mapuche_mi_simplificacion".
     * Si hay CUILs no insertados, los guarda en la propiedad "cuilsNoInserted" y muestra un mensaje con esa información.
     * Finalmente, inicia el siguiente paso del flujo de trabajo si existe.
     * @return void
     */
    #[On('success-mapuche-mi-simplificacion')]
    public function handleSuccessMapucheMiSimplificacion(): void
    {
        $this->workflowService->completeStep($this->processLog, self::STEP_EJECUTAR_FUNCION_ALMACENADA);

        $this->successMessage = 'Datos insertados en Mi Simplificacion';
        $this->miSimButton = false;

        $count = AfipMapucheMiSimplificacion::count();
        $this->successMessage = "Datos insertados en Mi Simplificacion: {$count}";

        $result = count($this->cuilsToSearch) - $count;
        $this->ShowMiSimplificacion = true;


        // Iniciar el siguiente paso del workflow
        $nextStep = $this->workflowService->getNextStep(self::STEP_EJECUTAR_FUNCION_ALMACENADA);


        if ($nextStep) {
            $this->workflowService->updateStep($this->processLog, $nextStep, 'in_progress');
        }
    }



    /** Recupera las CUIL (Clave Única de Identificación Laboral) que están presentes en la tabla temporal tabla_temp_cuils pero no en la tabla afip_mapuche_mi_simplificacion.
     *
     * @return array The array of CUILs that are present in the temporary table but not in the afip_mapuche_mi_simplificacion table.
     */
    private function cuilsNoEncontrados(): array
    {
        $cuilsNoEncontrados = DB::connection('pgsql-mapuche')
            ->table('suc.tabla_temp_cuils as ttc')
            ->leftJoin('suc.afip_mapuche_mi_simplificacion as amms', 'ttc.cuil', 'amms.cuil')
            ->whereNull('amms.cuil')
            ->pluck('ttc.cuil')
            ->toArray();

        return $cuilsNoEncontrados;
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
    private function restart()
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




    /** Carga los CUIL que no se encuentran en AFIP.
    *
    * Este método establece una estrategia de carga de CUIL que no se encuentran en AFIP, ejecuta la operación y actualiza varias propiedades de la clase Livewire con los resultados.
    *
    * - `cuilsNotInAfip`: La lista de CUIL que no se encuentran en AFIP.
    * - `showCuilsTable`: Indica si se debe mostrar la tabla de CUIL.
    * - `cuilsNotInAfipLoaded`: Indica si se han cargado los CUIL que no se encuentran en AFIP.
    * - `crearTablaTemp`: Indica si se debe crear una tabla temporal.
    * - `showCreateTempTableButton`: Indica si se debe mostrar el botón para crear la tabla temporal.
    */
    public function loadCuilsNotInAfip(): void
    {
        $this->setStrategy(
            new LoadCuilsNotInAfipStrategy(
                    $this->cuilRepository,
                    $this->workflowService,
                    $this->processLog)
                );
        $result = $this->executeOperation();

        if ($result) {
            $this->cuilsNotInAfip = $result['cuilsNotInAfip'];
            $this->showCuilsTable = $result['showCuilsTable'];
            $this->cuilsNotInAfipLoaded = $result['cuilsNotInAfipLoaded'];
            $this->crearTablaTemp = $result['crearTablaTemp'];
            $this->showCreateTempTableButton = $result['showCreateTempTableButton'];
        }
    }

    /** Compara las CUIL (Clave Única de Identificación Laboral) del modelo AfipMapucheSicoss con las CUIL del modelo AfipRelacionesActivas.
     *
     * Este método recupera todos los CUIL del modelo AfipRelacionesActivas, y luego encuentra todos los CUIL del modelo AfipMapucheSicoss
     * que no están presentes en el modelo AfipRelacionesActivas.
     * Los CUIL resultantes que no están en el modelo AfipRelacionesActivas se almacenan en la propiedad $cuilsNotInAfip.
     */
    #[Computed()]
    public function compareCuils()
    {
        $this->setStrategy(new CompareCuilsStrategy($this->cuilRepository));
    $result = $this->executeOperation();

    if ($result['success']) {
        $this->cuils = $result['cuils'];
        $this->success = $result['success'];
        $this->message = $result['message'];
    } else {
        $this->success = $result['success'];
        $this->message = $result['message'];
    }
    }
    private function paginateResults($collection, $perPage)
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $collection->slice(($currentPage - 1) * $perPage, $perPage)->all();
        return new LengthAwarePaginator(
            $currentPageItems,
            $collection->count(),
            $perPage
        );
    }



    /**
     * Busca la información de un empleado por su número de documento.
     *
     * Este método recupera la información del empleado con el número de documento proporcionado y la almacena en la propiedad $employeeInfo.
     * Además, establece la propiedad $showModal en true para mostrar un modal con la información del empleado.
     *
     * @param int $dni El número de documento del empleado a buscar.
     * @return void
     */
    public function searchEmployee($dni)
    {
        $this->selectedDni = $dni;
        $this->employeeInfo = $this->employeeService->searchEmployee($dni);
        $this->showModal = true;
    }
    /** Muestra los cargos asociados a un número de legajo específico.
    *
    * Este método recupera los cargos del empleado con el número de legajo proporcionado y los muestra en un modal.
    * Después de mostrar los cargos, cierra el modal principal.
    *
    * @param int $nroLegaj El número de legajo del empleado cuyos cargos se deben mostrar.
    * @return void
     */
    public function showCargos($nroLegaj): void
    {
        $this->cargos = $this->employeeService->getCargos($nroLegaj);

        $this->showCargoModal();
        $this->closeShowModal();
    }
    public function closeModal()
    {
        $this->showModal = false;
        $this->employeeInfo = null;
        $this->selectedDni = null;
    }
    public function showCargoModal()
    {
        $this->showCargoModal = true;
    }
    public function closeShowModal()
    {
        $this->showModal = false;
    }

    public function closeCargoModal()
    {
        $this->showCargoModal = false;
        $this->cargos = [];
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


    public function startNewProcess()
    {
        $this->processLog = $this->workflowService->startWorkflow();
    }

    public function getIsProcessCompleteProperty()
    {
        return $this->workflowService->isProcessCompleted($this->processLog);
    }




    public function render()
    {
        return view('livewire.compare-cuils');
    }
}
