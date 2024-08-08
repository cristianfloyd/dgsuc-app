<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\UploadedFile;
use App\Services\ColumnMetadata;
use App\Services\ValidationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\Contracts\FileProcessorInterface;
use App\Contracts\EmployeeServiceInterface;
use App\Contracts\WorkflowServiceInterface;
use App\Jobs\ImportAfipRelacionesActivasJob;
use App\Contracts\TransactionServiceInterface;

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
    protected $workflowService;
    private $validationService;
    private $columnMetadata;
    private $transactionService;
    private $showUploadForm;
    private $employeeService;


    public function boot(
        FileProcessorInterface $fileProcessor,
        EmployeeServiceInterface $employeeService,
        WorkflowServiceInterface $workflowService,
        ValidationService $validationService,
        ColumnMetadata $columnMetadata,
        TransactionServiceInterface $transactionService,
    ) {
        $this->fileProcessor = $fileProcessor;
        $this->workflowService = $workflowService;
        $this->validationService = $validationService;
        $this->columnMetadata = $columnMetadata;
        $this->transactionService = $transactionService;
        $this->employeeService = $employeeService;
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




    public function importar()
    {
        $this->validateFileSelection();

        try {
            // Verificar que el ID del archivo haya sido asignado
            if (!$this->archivoSeleccionadoId) {
                Log::error('Debe proporcionar un ID de archivo válido.');
                return;
            }

            // Ejecutar el comando Artisan para importar las relaciones activas
            // Artisan::call('afip:import', [
            //     'uploadedFileId' => $this->archivoSeleccionadoId, // Pasar el ID del archivo como argumento
            // ]);

            $uploadedFileId = $this->archivoSeleccionadoId;

            // Despachar el Job
            ImportAfipRelacionesActivasJob::dispatch($uploadedFileId);

            // Mostrar un mensaje de éxito si la importación fue correcta
            Log::info('El archivo se ha importado correctamente.');
            // Emitir un evento Livewire para actualizar la tabla
            $this->dispatch('show-success', ['message' => 'Se inició la importación en segundo plano.']);
        } catch (\Exception $e) {
            // Manejar cualquier excepción que ocurra durante la ejecución del comando
            Log::error('Hubo un error durante la importación: ' . $e->getMessage());
        }

    }

    private function validateFileSelection(): void
    {
        if (!$this->archivoSeleccionado) {
            throw new \InvalidArgumentException('No se ha seleccionado ningún archivo.');
        }

        $this->validationService->validateSelectedFile($this->archivoSeleccionado);
    }



    private function validateAndProcessFile(): void
    {
        $this->validateFileSelection();
        // Procesamiento del archivo usando FileProcessorService
        $lineasProcesadas = $this->fileProcessor->processFile(
            $this->archivoSeleccionado,
            $this->columnMetadata->getWidths()
        );

        $this->employeeService->storeProcessedLines($lineasProcesadas);
        $this->workflowService->completeStep($this->workflowService->getLatestWorkflow(), 'import_archivo_afip'); // Completar el paso
    }

    private function handleSuccessfulImport(object $processLog): void
    {
        $this->dispatch('show-success-message', ['message' => 'Se importó correctamente']);
        $this->dispatch('datos-importados');

        $currentStep = $this->workflowService->getCurrentStep($processLog);
        $this->nextStepUrl = $this->workflowService->getStepUrl($this->workflowService->getNextStep($currentStep));
    }









    private function handleImportError(\Exception $e, $processLog)
    {
        Log::error('Error en la importación: ' . $e->getMessage());
        $this->dispatch('show-error-message', ['message' => 'No se importó correctamente: ' . $e->getMessage()]);
        $this->workflowService->updateStep($processLog, 'import_archivo_afip', 'failed');
    }




    public function render()
    {

        if (!$this->showUploadForm) {
            return view('livewire.afip-relaciones-activas');
        } else {
            return view('livewire.uploadtxtcompleted', [
                'redirectUrl' => $this->nextStepUrl,
            ]);
        }
    }
}
