<?php

namespace App\Livewire;

use App\Contracts\TransactionServiceInterface;
use App\Contracts\WorkflowServiceInterface;
use App\Services\ColumnMetadata;
use App\Services\FileProcessorService;
use Livewire\Component;
use App\Models\UploadedFile;
use Illuminate\Support\Facades\Log;
use App\services\DatabaseService;
use App\Services\ValidationService;

/** Componente Livewire que maneja la importación de archivos AFIP y el almacenamiento de las relaciones activas.
 *
 * Este componente se encarga de:
 * - Mostrar la lista de archivos cargados y permitir la selección de uno de ellos.
 * - Procesar el archivo seleccionado, validar su contenido y almacenar las líneas procesadas en la base de datos.
 * - Manejar el flujo de trabajo de la importación, marcando los pasos correspondientes.
 * - Mostrar mensajes de éxito o error durante el proceso de importación.
 */
class AfipRelacionesActivas extends Component
{
    public $relacionesActivas;
    public $archivosCargados;
    public $archivoSeleccionado; //este es el archivo que se va a abrir en la vista
    public int $archivoSeleccionadoId; //este es el id del archivo que se va a abrir en la vista
    public $nextStepUrl = null;
    protected array $columnWidths = [
        6,  //periodo fiscal
        2,  //codigo movimiento
        2,  //Tipo de registro
        11,  //CUIL del empleado
        1,  //Marca de trabajador agropecuario
        3,  //Modalidad de contrato
        10,  //Fecha de inicio de la rel. Laboral
        10,  //Fecha de fin relacion laboral
        6,  //Código de obra social
        2,  //codigo situacion baja
        10,  //Fecha telegrama renuncia
        15,  //Retribución pactada
        1,  //Modalidad de liquidación
        5,  //Sucursal-Domicilio de desempeño
        6,  //Actividad en el domicilio de desempeño
        4,  //Puesto desempeñado
        1,  //Rectificación
        10,  //Numero Formulario Agropecuario
        3,  //Tipo de Servicio
        6,  //Categoría Profesional
        7,  //Código de Convenio Colectivo de Trabajo
        4,  //Sin valores, en blanco
    ];
    public array $lineasProcesadas; //este es el array que va a devolver la funcion procesarLinea
    public string $periodo_fiscal; //este es el periodo fiscal que se va a cargar en la tabla relaciones_activas

    private $fileProcessor;
    private $databaseService;
    protected $workflowService;
    private $validationService;
    private $columnMetadata;
    private $transactionService;
    private $showUploadForm;


    public function boot(
        FileProcessorService $fileProcessor,
        DatabaseService $databaseService,
        WorkflowServiceInterface $workflowService,
        ValidationService $validationService,
        ColumnMetadata $columnMetadata,
        TransactionServiceInterface $transactionService,
        ) {
            $this->fileProcessor = $fileProcessor;
            $this->databaseService = $databaseService;
            $this->workflowService = $workflowService;
            $this->validationService = $validationService;
            $this->columnMetadata = $columnMetadata;
            $this->transactionService = $transactionService;
            $this->checkCurrentStep();
        }

    public function mount()
    {
        $this->archivosCargados = UploadedFile::all();
    }

    public function updatedarchivoSeleccionadoId($value)
    {
        $this->archivoSeleccionado = $this->archivosCargados->find($value);
    }

    private function checkCurrentStep()
    {
        $processLog = $this->workflowService->getLatestWorkflow();
        $currentStep = $this->workflowService->getCurrentStep($processLog);
        $this->showUploadForm = in_array($currentStep, ['import_archivo_afip']);
        $this->nextStepUrl = $this->workflowService->getStepUrl($currentStep);
    }

    /** Importa un archivo AFIP y almacena las líneas procesadas en la base de datos.
     *
     * Este método se encarga de validar el archivo seleccionado, procesar su contenido y almacenar los datos en la base de datos.
     * También actualiza el estado del flujo de trabajo y envía mensajes de éxito o error según corresponda.
     *
     * @return void
     */
    public function importar():void
    {
        $processLog = $this->workflowService->getLatestWorkflow();
        $this->workflowService->updateStep($processLog, 'import_archivo_afip', 'in_progress');

        try {
            $this->transactionService->executeInTransaction(
                function () use ($processLog) {
                    $this->validateFileSelection();
                    $lineasProcesadas = $this->processFile();
                    $this->storeProcessedLines($lineasProcesadas);
                    $this->completeWorkflowStep($processLog);
                }
            );

            $this->dispatch('show-success-message', ['message' => 'Se importó correctamente']);
            $this->dispatch('datos-importados'); // Event para cargar RelacionesActivasTable

            $currentStep = $this->workflowService->getCurrentStep($processLog);
            $this->workflowService->getStepUrl($this->workflowService->getNextStep($currentStep) );
        } catch (\Exception $e) {
            $this->handleImportError($e, $processLog);
        }
    }

    private function validateFileSelection()
    {
        if (!$this->archivoSeleccionado) {
            throw new \Exception('No se ha seleccionado ningún archivo.');
        }

        $this->validationService->validateSelectedFile($this->archivoSeleccionado);
    }
    private function processFile()
    {
        return $this->fileProcessor->processFile($this->archivoSeleccionado, $this->columnMetadata->getWidths());
    }
    private function completeWorkflowStep($processLog)
    {
        $this->workflowService->completeStep($processLog, 'import_archivo_afip');
    }

    private function redirectToNextStep($currentStep)
    {
        $nextStep = $this->workflowService->getNextStep($currentStep);
        sleep(1);
        if ($nextStep) {
            return redirect()->to($this->workflowService->getStepUrl($nextStep));
        }
    }
    private function handleImportError(\Exception $e, $processLog)
    {
        Log::error('Error en la importación: ' . $e->getMessage());
        $this->dispatch('show-error-message', ['message' => 'No se importó correctamente: ' . $e->getMessage()]);
        $this->workflowService->updateStep($processLog, 'import_archivo_afip', 'failed');
    }

    private function storeProcessedLines($lineasProcesadas)
    {
        $datosMapeados = collect($lineasProcesadas)
            ->map(function ($linea) {
                return $this->databaseService->mapearDatosAlModelo($linea);
            })->all();

        $resultado = $this->databaseService->insertarDatosMasivos2($datosMapeados);
        $this->handleResultado($resultado);
    }

    private function almacenarLineas($lineasProcesadas)
    {
        $datosMapeados = collect($lineasProcesadas)
            ->map(function ($linea) {
                return $this->databaseService->mapearDatosAlModelo($linea);
            })->all();
        // dd($datosMapeados);

        $resultado = $this->databaseService->insertarDatosMasivos2($datosMapeados);
        $this->handleResultado($resultado);
    }

    private function handleResultado($resultado)
    {
        if ($resultado) {
            // mostrar un mensaje que se importo correctamente
            Log::info('Se importo correctamente');
            $this->dispatch('show-success-message', ['message' => 'Se importo correctamente']);
        } else {
            // mostrar un mensaje que no se importo correctamente
            Log::error('No se importo correctamente');
            $this->dispatch('show-error-message', ['message' => 'No se importo correctamente']);
        }
    }


    public function render()
    {

        if ($this->showUploadForm) {
            return view('livewire.afip-relaciones-activas');
        } else {
            return view('livewire.uploadtxtcompleted', [
                'redirectUrl' => $this->nextStepUrl,
            ]);
        }
    }
}
