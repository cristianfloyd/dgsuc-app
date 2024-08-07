<?php

namespace App\Livewire;

use App\Contracts\WorkflowServiceInterface;
use Exception;
use Livewire\Component;
use App\Models\UploadedFile;
use App\Models\OrigenesModel;
use App\Services\UploadService;
use App\Services\WorkflowService;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;

class Uploadtxt extends Component
{
    use WithFileUploads;
    #[Rule('max:20480')] // 20MB Max
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

    public function boot(WorkflowServiceInterface $workflowService)
    {
        $this->workflowService = $workflowService;
        $this->checkCurrentStep();
    }

    public function mount()
    {
        $this->importaciones = UploadedFile::all();
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
    public function uploadfilemodel(): void
    {
        $file = $this->archivotxt;
        $originalName = $this->archivotxt->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $time = time();
        $file_path = $this->file_path;
        $filename = "{$originalName}_$time.$extension";


        $this->archivoModel = new UploadedFile();
        $this->archivoModel->filename = basename($this->file_path);
        $this->archivoModel->original_name = $file->getClientOriginalName();
        $this->archivoModel->file_path = $file_path;
        $this->archivoModel->periodo_fiscal = $this->periodo_fiscal;

        $this->archivoModel->origen = OrigenesModel::find($this->selectedOrigen)->name;
        $this->archivoModel->user_id = auth()->user()->id;
        $this->archivoModel->user_name = auth()->user()->name;
        // Log::info($this->archivoModel->file_path);
        $this->archivoModel->save();
    }



    public function save()
    {
        try {
            $this->validateAndPrepare();
            $this->processFileUpload();
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

    private function updateWorkflowStep()
    {
        $stepToComplete = $this->currentStep === 'subir_archivo_afip' ? 'subir_archivo_afip' : 'subir_archivo_mapuche';
        $this->workflowService->completeStep($this->processLog, $stepToComplete);
        Log::info("Paso completado: {$stepToComplete}");
    }

    private function handleNextStep()
    {
        $nextStep = $this->workflowService->getNextStep($this->currentStep);
        if ($nextStep) {
            $this->dispatch('paso-completado', ['step' => $this->stepKey]);
            $this->redirect(route('afip-mi-simplificacion'));
            Log::info("Redirigiendo al siguiente paso: {$nextStep}");
        }
    }

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

    /**
     * Elimina un archivo cargado previamente.
     *
     * Esta función se encarga de eliminar un archivo cargado previamente del servidor y de la base de datos.
     * Primero, busca el archivo en la base de datos utilizando el ID proporcionado. Luego, intenta eliminar el archivo
     * del servidor. Si la eliminación es exitosa, también se elimina el registro de la base de datos. Si ocurre
     * algún error durante el proceso, se envían los mensajes de error correspondientes.
     *
     * @param int $fileId El ID del archivo a eliminar.
     * @return void
     */
    public function deleteFile($fileId)
    {
        try {
            $file = UploadedFile::findOrFail($fileId);

            // Eliminar el archivo del servidor
            $deleted = UploadService::deleteFile($file->file_path);
            // dd($deleted);
            $this->dispatch('fileDeleted');

            if ($deleted) {
                // Si el archivo se elimino correctamente, eliminar el registro de la base de datos
                $file->delete();
                $this->dispatch('success', 'Archivo eliminado correctamente.');
            } else {
                $this->dispatch('error', 'Error al eliminar el archivo.');
            }

            // Actualizar la lista de archivos
            $this->importaciones = UploadedFile::all();
        } catch (Exception $e) {
            //Handle file deletion or other unexpected errors
            $this->dispatch('fileDeleteError', $e->getMessage());
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
