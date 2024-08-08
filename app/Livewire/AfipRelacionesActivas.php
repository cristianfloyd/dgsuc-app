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





public function importar(): void
{
    $processLog = $this->workflowService->getLatestWorkflow();
    $this->workflowService->updateStep($processLog, 'import_archivo_afip', 'in_progress');

    try {
        $this->transactionService->executeInTransaction(
            function () use ($processLog) {
                $this->validateAndProcessFile($processLog);
            }
        );

        $this->handleSuccessfulImport($processLog);
    } catch (\Exception $e) {
        $this->handleImportError($e, $processLog);
    }
}

private function validateAndProcessFile(object $processLog): void
{
    $this->validateFileSelection();
    $lineasProcesadas = $this->processFile();
    $this->storeProcessedLines($lineasProcesadas);
    $this->completeWorkflowStep($processLog);
}

private function handleSuccessfulImport(object $processLog): void
{
    $this->dispatch('show-success-message', ['message' => 'Se importó correctamente']);
    $this->dispatch('datos-importados');

    $currentStep = $this->workflowService->getCurrentStep($processLog);
    $this->nextStepUrl = $this->workflowService->getStepUrl($this->workflowService->getNextStep($currentStep));
}

private function validateFileSelection(): void
{
    if (!$this->archivoSeleccionado) {
        throw new \InvalidArgumentException('No se ha seleccionado ningún archivo.');
    }

    $this->validationService->validateSelectedFile($this->archivoSeleccionado);
}

private function processFile(): array
{
    return $this->fileProcessor->processFile($this->archivoSeleccionado, $this->columnMetadata->getWidths());
}

private function storeProcessedLines(array $lineasProcesadas): void
{
    $datosMapeados = $this->mapearDatos($lineasProcesadas);
    $resultado = $this->databaseService->insertarDatosMasivos2($datosMapeados);
    $this->handleResultado($resultado);
}

private function mapearDatos(array $lineasProcesadas): array
{
    return collect($lineasProcesadas)
        ->map(fn($linea) => $this->databaseService->mapearDatosAlModelo($linea))
        ->all();
}

private function handleResultado(bool $resultado): void
{
    $message = $resultado ? 'Se importó correctamente' : 'No se importó correctamente';
    $eventName = $resultado ? 'show-success-message' : 'show-error-message';

    Log::info($message);
    $this->dispatch($eventName, ['message' => $message]);
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
