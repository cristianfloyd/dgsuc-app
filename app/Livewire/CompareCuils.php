<?php

namespace App\Livewire;

use App\Models\Dh01;
use App\Models\Dh03;
use App\Models\TablaTempCuils;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\AfipMapucheSicoss;
use App\Services\WorkflowService;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AfipRelacionesActivas;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\AfipMapucheMiSimplificacion;
use Illuminate\Pagination\LengthAwarePaginator;

class CompareCuils extends Component
{
    use WithPagination;

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
    protected $workflowService;
    protected $currentStep;



    public function boot(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
        $this->perPage = self::PER_PAGE;
    }

    public function mount()
    {
        $processLog = $this->workflowService->getLatestWorkflow();
        // dd($processLog);
        if ($processLog) {
            $this->currentStep = $this->workflowService->getCurrentStep($processLog);
            Log::info("mount: currentStep: {$this->currentStep}");

            // dd($this->currentStep);
            if ($this->currentStep === 'poblar_tabla_temp_cuils') {
                $this->crearTablaTemp = true;
                $this->cuilsCount = TablaTempCuils::count();
                if ($this->cuilsCount == 0) {
                    // volver al paso anterior
                    $this->workflowService->updateStep($processLog, 'obtener_cuils_not_in_afip', 'in_pprogress');
                    log::info("mount: volver al paso anterior");
                }
            } else if ($this->currentStep === 'ejecutar_funcion_almacenada') {
                $this->crearTablaTemp = true;
            } else if ($this->currentStep === 'obtener_cuils_no_insertados' || $this->currentStep === 'exportar_txt_para_afip') {

                $this->loadCuilsNotInserted();
            } else {
                //metodo para mostrar tabla de cuils no encontrados y componente paraMiSimplificacion
            }
        } else {
            $this->currentStep = null;
        }
    }

    #[Computed]
    public function showLoadButton()
    {
        return $this->currentStep === 'obtener_cuils_not_in_afip';
    }



    #[Computed]
    public function showExecuteStoredFunctionButton()
    {
        return $this->currentStep === 'ejecutar_funcion_almacenada';
    }


    public function showParaMiSimplificacionAndCuilsNoEncontrados()
    {
        $this->ShowMiSimplificacion = true;
        $this->loadCuilsNotInserted();
        $this->showCuilsNoEncontrados = true;
        $this->showCuilsTable = false;

        // Asegúrate de que $cuilsNoInserted esté poblado con los CUILs no encontrados
        if (empty($this->cuilsNoInserted)) {
            // Aquí puedes agregar lógica para poblar $cuilsNoInserted si es necesario
        }

        $this->dispatch('content-updated');
    }

    #[Computed]
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
        $processLog = $this->workflowService->getLatestWorkflow();
        $this->workflowService->updateStep($processLog, 'ejecutar_funcion_almacenada', 'in_progress');

        $this->dispatch('mapuche-mi-simplificacion', $this->nroLiqui, $this->periodoFiscal);
        $this->reset('cuilsNotInAfipLoaded');
    }

    public function showCuilsDetails(): void
    {
        $processLog = $this->workflowService->getLatestWorkflow();

        $currentStep = $this->workflowService->getCurrentStep($processLog);

        Log::info("showCuilsDetails currentStep: {$currentStep} | processLog: {$processLog->id}");

        if ($currentStep === 'poblar_tabla_temp_cuils') {
            $this->workflowService->updateStep($processLog, 'poblar_tabla_temp_cuils', 'in_progress');
            $this->dispatch('iniciar-poblado-tabla-temp', $this->nroLiqui, $this->periodoFiscal, $this->cuilsToSearch);
            Log::info("showCuilsDetails: iniciar-poblado-tabla-temp");
        }
    }


    /** Maneja el éxito de la población de la tabla temporal de CUILs.
     * Completa el paso 'poblar_tabla_temp_cuils' en el registro de flujo de trabajo y actualiza el paso 'ejecutar_funcion_almacenada' a 'in_progress'.
     * Luego, llama al método 'ejecutarFuncionAlmacenada()' para iniciar el siguiente paso del flujo de trabajo.
     */
    #[On('success-tabla-temp-cuils')]
    public function handleTablaTempCuilsSuccess()
    {
        $processLog = $this->workflowService->getLatestWorkflow();
        $this->workflowService->completeStep($processLog, 'poblar_tabla_temp_cuils');

        // Iniciar el siguiente paso: ejecutar_funcion_almacenada
        $this->workflowService->updateStep($processLog, 'ejecutar_funcion_almacenada', 'in_progress');
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
     */
    #[On('success-mapuche-mi-simplificacion')]
    public function handleSuccessMapucheMiSimplificacion(): void
    {
        $processLog = $this->workflowService->getLatestWorkflow();
        $this->workflowService->completeStep($processLog, 'ejecutar_funcion_almacenada');

        $this->successMessage = 'Datos insertados en Mi Simplificacion';
        $this->miSimButton = false;

        $count = AfipMapucheMiSimplificacion::count();
        $this->successMessage = "Datos insertados en Mi Simplificacion: {$count}";

        $result = count($this->cuilsToSearch) - $count;
        $this->ShowMiSimplificacion = true;

        if ($result > 0) {
            $this->loadCuilsNotInserted();
            $this->successMessage .= ". CUILs no insertados: " . count($this->cuilsNoInserted);
            $this->reset('cuilsNotInAfipLoaded', 'showCuilsTable');
            $this->showCuilsNoEncontrados = true;
        }

        // Iniciar el siguiente paso del workflow
        $nextStep = $this->workflowService->getNextStep('ejecutar_funcion_almacenada');


        if ($nextStep) {
            $this->workflowService->updateStep($processLog, $nextStep, 'in_progress');
        }
    }






    /** Recupera las CUIL (Clave Única de Identificación Laboral) que están presentes en la tabla temporal tabla_temp_cuils pero no en la tabla afip_mapuche_mi_simplificacion.
     *
     * @return array The array of CUILs that are present in the temporary table but not in the afip_mapuche_mi_simplificacion table.
     */
    public function cuilsNoEncontrados(): array
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

        $processLog = $this->workflowService->getLatestWorkflow();

        log::info("processLog: {$processLog->id}");

        if (!$processLog) {
            $processLog = $this->workflowService->startWorkflow();
        }

        $currentStep = $this->workflowService->getCurrentStep($processLog);

        log::info("currentStep: {$currentStep}");

        if ($currentStep === 'obtener_cuils_not_in_afip') {
            // Aquí va la lógica existente
            $this->showCuilsTable = true;
            Log::info('CurrentStep es {$currentStep}, se carga tabla de cuils no encontrados');
            $this->cuilsNotInAfipLoaded = $this->toggleValue($this->cuilsNotInAfipLoaded);
            $this->crearTablaTemp = $this->toggleValue($this->crearTablaTemp);
            Log::info('compareCuils iniciado');

            // Iniciamos el proceso de comparación de CUILs
            $this->compareCuils();

            // Marcamos el paso como completado
            $this->workflowService->completeStep($processLog, $currentStep);
            Log::info("completado: {$currentStep}");
            $this->showCreateTempTableButton = true;
        } else {
            // Estamos en el paso incorrecto, obtener la url y redireccionar
            $url = $this->workflowService->getStepUrl($currentStep);
            Log::warning("url: {$url}");
        }
    }

    /** Compara las CUIL (Clave Única de Identificación Laboral) del modelo AfipMapucheSicoss con las CUIL del modelo AfipRelacionesActivas.
     *
     * Este método recupera todos los CUIL del modelo AfipRelacionesActivas, y luego encuentra todos los CUIL del modelo AfipMapucheSicoss
     * que no están presentes en el modelo AfipRelacionesActivas.
     * Los CUIL resultantes que no están en el modelo AfipRelacionesActivas se almacenan en la propiedad $cuilsNotInAfip.
     */
    #[Computed()]
    public function compareCuils(): LengthAwarePaginator
    {
        try {
            $this->cuilsNotInAfip = AfipMapucheSicoss::select('cuil')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('suc.afip_relaciones_activas')
                        ->whereColumn('afip_relaciones_activas.cuil', 'afip_mapuche_sicoss.cuil');
                })
                ->pluck('cuil');

            $this->cuilsToSearch = $this->cuilsNotInAfip->toArray();

            // contar los campos en cuilsToSearch
            $this->cuilsCount = count($this->cuilsToSearch);

            return $this->paginateResults($this->cuilsNotInAfip, $this->perPage);
        } catch (QueryException $e) {
            Log::error('Error en la consulta de comparación de CUILs: ' . $e->getMessage());
            throw new \Exception('Error al procesar la comparación de CUILs. Por favor, inténtelo de nuevo más tarde.');
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
