<?php

namespace App\Livewire;

use App\Contracts\DatabaseServiceInterface;
use App\Contracts\EmployeeServiceInterface;
use App\Contracts\FileProcessorInterface;
use App\Contracts\TableManagementServiceInterface;
use App\Contracts\TransactionServiceInterface;
use App\Contracts\WorkflowServiceInterface;
use App\Jobs\ImportAfipRelacionesActivasJob;
use App\Models\UploadedFile;
use App\Services\ColumnMetadata;
use App\Services\ValidationService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

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

    public $archivosCargados = [];

    public $archivoSeleccionado; //este es el archivo que se va a abrir en la vista

    public int $archivoSeleccionadoId; //este es el id del archivo que se va a abrir en la vista

    public $nextStepUrl;

    public array $processedLines; //este es el array que va a devolver la funcion processLine

    public string $periodo_fiscal; //este es el periodo fiscal que se va a cargar en la tabla relaciones_activas

    private $fileProcessor;

    private $workflowService;

    private $validationService;

    private $columnMetadata;

    private $transactionService;

    private $showUploadForm;

    private $employeeService;

    private $uploadedFileModel;

    public function boot(
        FileProcessorInterface $fileProcessor,
        EmployeeServiceInterface $employeeService,
        ValidationService $validationService,
        TransactionServiceInterface $transactionService,
        WorkflowServiceInterface $workflowService,
        ColumnMetadata $columnMetadata,
        UploadedFile $uploadedFileModel,
    ): void {
        Log::info('Se ha iniciado el componente AfipRelacionesActivas');
        $this->fileProcessor = $fileProcessor;
        $this->workflowService = $workflowService;
        $this->validationService = $validationService;
        $this->columnMetadata = $columnMetadata;
        $this->transactionService = $transactionService;
        $this->employeeService = $employeeService;
        $this->uploadedFileModel = $uploadedFileModel;
        $this->checkCurrentStep();
    }

    public function mount(): void
    {
        $this->archivosCargados = $this->uploadedFileModel->all()
            ->map(function ($archivo) {
                return [
                    'id' => $archivo->id,
                    'original_name' => $archivo->original_name . ' (' . $archivo->periodo_fiscal . ')',
                ];
            });
    }

    public function updatedarchivoSeleccionadoId($value): void
    {
        $this->archivoSeleccionado = $this->uploadedFileModel->find($value);
    }

    public function importar(?int $archivoId = null): void
    {
        if ($archivoId !== null) {
            $this->archivoSeleccionadoId = $archivoId;
            $this->archivoSeleccionado = $this->uploadedFileModel()->find($archivoId);
            Log::info("importar() en AfipRelacionesActivas \$archivoId pasado como argumento: $archivoId");
        }
        $this->validateFileSelection();

        try {
            // Verificar que el ID del archivo haya sido asignado
            if (!$this->archivoSeleccionadoId) {
                Log::error('Debe proporcionar un ID de archivo válido.');
                return;
            }



            $uploadedFileId = $this->archivoSeleccionadoId;


            // Despachar el Job
            ImportAfipRelacionesActivasJob::dispatchSync(
                $this->fileProcessor,
                $this->employeeService,
                $this->validationService,
                $this->transactionService,
                $this->workflowService,
                $this->columnMetadata,
                app(DatabaseServiceInterface::class),        // Inyectamos el servicio
                app(TableManagementServiceInterface::class), // Inyectamos el servicio
                $uploadedFileId,
            );

            // Mostrar un mensaje de éxito si la importación fue correcta
            Log::info('El archivo se ha importado correctamente.');

            // Emitir un evento Livewire para actualizar la tabla
            $this->dispatch('show-success', ['message' => 'Se inició la importación en segundo plano.']);
        } catch (\Exception $e) {
            // Manejar cualquier excepción que ocurra durante la ejecución del comando
            Log::error('Hubo un error durante la importación: ' . $e->getMessage());
        }
    }

    public function render()
    {
        if ($this->showUploadForm) {
            return view('livewire.afip-relaciones-activas');
        }
        return view('livewire.uploadtxtcompleted', [
            'redirectUrl' => $this->nextStepUrl,
        ]);
    }

    /**
     * Set the value of archivoSeleccionado.
     *
     * @return self
     */
    public function setArchivoSeleccionado($archivoSeleccionado)
    {
        $this->archivoSeleccionado = $archivoSeleccionado;

        return $this;
    }

    private function checkCurrentStep(): void
    {
        $processLog = $this->workflowService->getLatestWorkflow();
        $currentStep = $this->workflowService->getCurrentStep($processLog);
        $this->showUploadForm = \in_array($currentStep, ['import_archivo_afip']);
        $this->nextStepUrl = $this->workflowService->getStepUrl($currentStep);
    }

    private function validateFileSelection(): void
    {
        if (!$this->archivoSeleccionado) {
            throw new \InvalidArgumentException('No se ha seleccionado ningún archivo.');
        }

        $this->validationService->validateSelectedFile($this->archivoSeleccionado);
    }
}
