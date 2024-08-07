<?php

namespace App\Livewire;

use Exception;
use Livewire\Component;
use App\Models\UploadedFile;
use App\Models\OrigenesModel;
use Livewire\WithFileUploads;
use App\Services\UploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Contracts\WorkflowServiceInterface;
use App\Contracts\OrigenRepositoryInterface;
use Illuminate\Validation\ValidationException;
use App\Contracts\FileUploadRepositoryInterface;
use App\Services\FileUploadService;

class Uploadtxt extends Component
{
    use WithFileUploads;


    public $archivotxt;

    public $headers = [];
    public $importaciones;
    public $archivoModel = [];
    public $file_path;
    public $periodo_fiscal;
    public $origenes = ['afip', 'mapuche'];
    public $selectedOrigen;
    public $showUploadForm = false;
    public $nextStepUrl = null;

    protected $workflowService;
    protected $processLog;
    protected $currentStep;
    private $fileUploadRepository;
    private $origenRepository;
    private $fileUploadService;
    /**
     * Constructor del componente.
     *
     * @param FileUploadRepositoryInterface $fileUploadRepository
     * @param OrigenRepositoryInterface $origenRepository
     */
    public function boot(
        WorkflowServiceInterface $workflowService,
        FileUploadRepositoryInterface $fileUploadRepository,
        OrigenRepositoryInterface $origenRepository,
        FileUploadService $fileUploadService
    ){
        $this->workflowService = $workflowService;
        $this->fileUploadRepository = $fileUploadRepository;
        $this->origenRepository = $origenRepository;
        $this->fileUploadService = $fileUploadService;

        $this->checkCurrentStep();
    }

    public function mount()
    {
        $this->importaciones = $this->fileUploadRepository->all();
        $this->origenes = OrigenesModel::all();
    }

    /**
     * Comprueba el paso actual en el flujo de trabajo y actualiza las propiedades de la vista.
     *
     * Este método realiza las siguientes tareas:
     * 1. Obtiene el último registro de flujo de trabajo utilizando el WorkflowService.
     * 2. Obtiene el paso actual en el flujo de trabajo utilizando el WorkflowService.
     * 3. Establece la propiedad 'showUploadForm' en función de si el paso actual es 'subir_archivo_afip' o 'subir_archivo_mapuche'.
     * 4. Establece la propiedad 'nextStepUrl' con la URL del siguiente paso en el flujo de trabajo.
     * @return void
     */
    public function checkCurrentStep(): void
    {
        $processLog = $this->workflowService->getLatestWorkflow();
        $currentStep = $this->workflowService->getCurrentStep($processLog);
        $this->showUploadForm = in_array($currentStep, ['subir_archivo_afip', 'subir_archivo_mapuche']);
        $this->nextStepUrl = $this->workflowService->getStepUrl($currentStep);
    }


    /**
     * Guarda un nuevo modelo de archivo cargado en la base de datos.
     *
     * Este método realiza las siguientes tareas:
     * 1. Obtiene los detalles del archivo cargado, como el nombre original, la extensión y la ruta del archivo.
     * 2. Crea un nuevo modelo de UploadedFile con los detalles del archivo.
     * 3. Establece los atributos del modelo, como el nombre de archivo, la ruta del archivo, el período fiscal y el origen.
     * 4. Establece el usuario que cargó el archivo y guarda el modelo en la base de datos.
     * @return void
     */
    public function uploadFileModel(): void
    {
        $this->validate([
            'archivotxt' => 'required|file',
            'file_path' => 'required|string',
            'periodo_fiscal' => 'required|string',
            'selectedOrigen' => 'required|exists:origenes,id',
        ]);

        DB::transaction(function () {
            $origen = $this->origenRepository->findById($this->selectedOrigen);

            $this->fileUploadRepository->create([
                'filename' => basename($this->file_path),
                'original_name' => $this->archivotxt->getClientOriginalName(),
                'file_path' => $this->file_path,
                'periodo_fiscal' => $this->periodo_fiscal,
                'origen' => $origen->name,
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
            ]);
        });
    }




    /**
     * Guarda un archivo cargado en la base de datos y actualiza el flujo de trabajo.
     *
     * Este método realiza las siguientes tareas:
     * 1. Valida y prepara los datos de entrada.
     * 2. Procesa la carga del archivo.
     * 3. Actualiza el paso actual en el flujo de trabajo y redirige al siguiente paso.
     *
     * @return void
     */
    public function save()
    {
        try {
            $this->validateAndPrepare();

            // 1. Cargar el archivo en el servidor
            $filePath = $this->fileUploadService->uploadFile($this->archivotxt, 'afiptxt');

            if (!$filePath) {
                throw new Exception('Error al cargar el archivo en el servidor.');
            }

            // 2. Almacenar en la base de datos del modelo UploadedFile
            $uploadedFile = $this->fileUploadRepository->create([
                'filename' => basename($filePath),
                'original_name' => $this->archivotxt->getClientOriginalName(),
                'file_path' => $filePath,
                'periodo_fiscal' => $this->periodo_fiscal,
                'origen' => $this->origenRepository->findById($this->selectedOrigen)->name,
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
            ]);

            if (!$uploadedFile) {
                throw new Exception('Error al guardar la información del archivo en la base de datos.');
            }

            // 3. Actualizar el flujo de trabajo y redirigir
            $this->updateWorkflowAndRedirect();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    private function validateAndPrepare()
    {
        $this->validateInput();
        $this->processLog = $this->workflowService->getLatestWorkflow();
        $this->currentStep = $this->workflowService->getCurrentStep($this->processLog);
        Log::info("Proceso actual: {$this->processLog}, Paso actual: {$this->currentStep}");
    }

    /**
     * Valida los datos de entrada del archivo que se va a subir.
     * Verifica que el archivo sea requerido, que sea de tipo .txt o .csv, y que no supere los 20MB.
     * Si alguna de estas validaciones falla, se lanzará una excepción de validación con los mensajes de error correspondientes.
     *
     * @return void
     */
    private function validateInput(): void
    {
        $messages = [
            'archivotxt.required' => 'Por favor, seleccione un archivo para subir.',
            'archivotxt.mimes' => 'Solo se permiten archivos de tipo .txt.',
            'archivotxt.max' => 'El archivo no debe superar los 20MB.',
        ];

        $this->validate([
            'archivotxt' => 'required|file|mimes:txt,csv|max:20480',
        ], $messages);
    }

    private function updateWorkflowAndRedirect()
    {
        $this->updateWorkflowStep();
        $this->resetForm();
        $this->handleNextStep();
    }

    private function resetForm()
        {
            $this->archivotxt = null;
            $this->periodo_fiscal = '';
            $this->file_path = '';
            $this->resetValidation();
        }

    private function updateWorkflowStep()
    {
        $stepToComplete = $this->currentStep === 'subir_archivo_afip' ? 'subir_archivo_afip' : 'subir_archivo_mapuche';
        $this->workflowService->completeStep($this->processLog, $stepToComplete);
        Log::info("Paso completado updateWorkflowStep(): {$stepToComplete}");
    }

    private function handleNextStep()
    {
        $nextStep = $this->workflowService->getNextStep($this->currentStep);
        if ($nextStep) {
            $this->dispatch('paso-completado');
            $this->redirect(route('MiSimplificacion'));
            Log::info("(handleNextStep) Redirigiendo al siguiente paso: {$nextStep}");
        }
    }

    /**
     * Maneja una excepción que ocurre durante la subida de un archivo.
     *
     * Esta función se encarga de manejar las excepciones que pueden ocurrir durante la subida de un archivo.
     * Dependiendo del tipo de excepción, se envía un evento al frontend con el tipo de error y el mensaje de error correspondiente.
     * También se registra el error en el log de la aplicación.
     *
     * @param Exception $e La excepción que se produjo.
     * @return void
     */
    private function handleException(Exception $e)
    {
        $errorType = $e instanceof ValidationException ? 'validationError' : 'fileUploadError';
        $errorMessage = $e instanceof ValidationException ? $e->errors() : $e->getMessage();
        $this->dispatch($errorType, $errorMessage);
        Log::error("Error en save(): {$e->getMessage()}");
    }

    /**
     * Sube un archivo al servidor.
     *
     * Esta función se encarga de subir un archivo al servidor utilizando el servicio UploadService.
     * El archivo a subir se pasa como parámetro en la propiedad 'archivotxt' y se guarda en la carpeta 'afiptxt'.
     *
     * @return string La ruta del archivo subido.
     */
    private function uploadFile()
    {
        return UploadService::uploadFile($this->archivotxt, 'afiptxt');
    }


    public function deleteFile($fileId)
    {
        try {
            DB::transaction(function () use ($fileId) {
                // Find the file or throw an exception if not found
                $file = $this->uploadedFileRepository->findOrFail($fileId);

                // Attempt to delete the file from the server
                if ($this->fileUploadService->deleteFile($file->file_path)) {
                    // If successful, delete the database record
                    $this->uploadedFileRepository->delete($file);
                    $this->dispatch('success', 'Archivo eliminado correctamente.');
                } else {
                    throw new Exception('Error al eliminar el archivo del servidor.');
                }
            });

            // Notify the frontend that a file was deleted
            $this->dispatch('fileDeleted');

            // Refresh the list of uploaded files
            $this->importaciones = $this->uploadedFileRepository->all();
        } catch (Exception $e) {
            $this->dispatch('error', 'Error: ' . $e->getMessage());
        }
    }

    public function updatedImportaciones()
    {
        //
    }


    public function render()
    {
        if ($this->showUploadForm) {
            return view('livewire.uploadtxt');
        } else {
            return view('livewire.uploadtxtcompleted', [
                'redirectUrl' => $this->nextStepUrl,
            ]);
        }
    }
}
