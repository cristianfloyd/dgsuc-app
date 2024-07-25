<?php

namespace App\Livewire;

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

    protected $workflowService;

    public function boot(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Carga un archivo al servidor y crea un nuevo modelo UploadedFile con los detalles del archivo para almacenar en la base de datos.
     *
     * @return void
     */
    public function uploadfilemodel()
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
        $this->archivoModel->save();
    }


    /**
     * Saves the uploaded file, creates a new UploadedFile model, and updates the workflow.
     *
     * This method is responsible for the following tasks:
     * 1. Validates the uploaded file, ensuring it is a .txt file and does not exceed 20MB.
     * 2. Uploads the file using the UploadService and stores the file path.
     * 3. Creates a new UploadedFile model with the file details and saves it to the database.
     * 4. Retrieves or starts a new workflow using the WorkflowService.
     * 5. Marks the 'subir_archivo_afip' step as completed in the workflow.
     * 6. Redirects to the next step in the workflow, if available.
     * 7. Resets the 'archivotxt' property to clear the form.
     *
     * @throws ValidationException if the file validation fails
     * @throws Exception if there is an error during file upload or other unexpected errors
     */
    public function save()
    {
        // obtenemos el proceso actual o creamos uno nuevo si no existe.
        $processLog = $this->workflowService->getLatestWorkflow() ?? $this->workflowService->startWorkflow();
        Log::info("obtenemos el proceso actual o creamos uno nuevo si no existe. processLog: $processLog");
        $currentStep = $this->workflowService->getCurrentStep($processLog);

        Log::info('Archivo subido: ' . $this->archivotxt->getClientOriginalName());

        $messages = [
            'archivotxt.required' => 'Por favor, seleccione un archivo para subir.',
            'archivotxt.mimes' => 'Solo se permiten archivos de tipo .txt.',
            'archivotxt.max' => 'El archivo no debe superar los 20MB.',
        ];

        $this->validate([
            'archivotxt' => 'required|file|mimes:txt,csv|max:20480',
        ], $messages);

        try {

            // Usar el UploadService para almacenar el archivo
            $this->file_path = UploadService::uploadFile( $this->archivotxt, 'afiptxt');

            // dd($this->file_path);
            $this->uploadfilemodel();



            // Marcamos el paso como completado
            switch ($currentStep) {
                case 'subir_archivo_afip':
                    $this->workflowService->completeStep($processLog, 'subir_archivo_afip');
                    break;
                case 'subir_archivo_mapuche':
                    $this->workflowService->completeStep($processLog, 'subir_archivo_mapuche');
                    break;
            }

            // Redirigir al siguiente paso. En este caso es el mismo componente livewire. Pero ahora hay continuar con el paso 'subir_archivo_mapuche'
            $nextStep = $this->workflowService->getNextStep($currentStep);

            Log::info("nextStep: $nextStep");
            if ($nextStep) {
                return redirect()->to(
                    $this->workflowService->getStepUrl($nextStep)
                );
            }

            // Actualizar la lista de archivos
            $this->importaciones = UploadedFile::all();

            // Resetear la propiedad para limpiar el formulario
            $this->reset('archivotxt');

        } catch (ValidationException $e) {
            //Handle validation errors
            $this->dispatch('validationError', $e->errors());

        } catch (Exception $e) {
            //Handle file upload or other unexpected errors
            $this->dispatch('fileUploadError', $e->getMessage());
        }
    }

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

    public function mount()
    {
        $this->importaciones = UploadedFile::all();
        //obtener los origenes de la base de datos OrigenesModel
        $this->origenes = OrigenesModel::all();
    }
    public function render()
    {
        return view('livewire.uploadtxt');
    }
}
