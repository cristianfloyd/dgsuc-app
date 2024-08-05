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
    public $showUploadForm = false;
    public $nextStepUrl = null;

    protected $workflowService;

    public function boot(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
        $this->checkCurrentStep();
    }

    public function mount()
    {
        $this->importaciones = UploadedFile::all();
        $this->origenes = OrigenesModel::all();
    }

    public function checkCurrentStep()
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


    /** Guarda el archivo subido, crea un nuevo modelo UploadedFile y actualiza el flujo de trabajo.
     *
     * Este método es responsable de las siguientes tareas:
     * 1. Valida el archivo subido, asegurándose de que sea un archivo .txt y no exceda los 20MB.
     * 2. Sube el archivo utilizando el UploadService y almacena la ruta del archivo.
     * 3. Crea un nuevo modelo UploadedFile con los detalles del archivo y lo guarda en la base de datos.
     * 4. Recupera o inicia un nuevo flujo de trabajo utilizando el WorkflowService.
     * 5. Marca el paso 'subir_archivo_afip' como completado en el flujo de trabajo.
     * 6. Redirige al siguiente paso en el flujo de trabajo, si está disponible.
     * 7. Reinicia la propiedad 'archivotxt' para limpiar el formulario.
     *
     * @throws ValidationException si la validación del archivo falla
     * @throws Exception si hay un error durante la subida del archivo u otros errores inesperados
     */
    public function save()
    {
        // obtenemos el proceso actual.
        $processLog = $this->workflowService->getLatestWorkflow();
        Log::info("obtenemos el proceso actual: $processLog");
        $currentStep = $this->workflowService->getCurrentStep($processLog);

        $this->validateInput();

        try {

            // Usar el UploadService para almacenar el archivo
            $this->file_path = $this->uploadFile();
            // Guardar el archivo en la base de datos
            $this->uploadfilemodel();

            Log::info("Antes de actualizar la tabla de archivos");
            // Actualizar la tabla de archivos
            $this->importaciones = UploadedFile::all();

            // Resetear la propiedad para limpiar el formulario
            $this->reset('archivotxt', 'selectedOrigen', 'periodo_fiscal');

            // Marcamos el paso como completado
            switch ($currentStep) {
                case 'subir_archivo_afip':
                    $this->workflowService->completeStep($processLog, 'subir_archivo_afip');
                    break;
                case 'subir_archivo_mapuche':
                    $this->workflowService->completeStep($processLog, 'subir_archivo_mapuche');
                    break;
            }

            // Redirigir al siguiente paso porque se cargaron los 2 archivos
            $nextStep = $this->workflowService->getNextStep($currentStep);
            if ($nextStep) {
                // Notificar al componente principal
                $this->dispatch('paso-completado', ['step' => $this->stepKey]);
                // Redirigir de vuelta al componente principal
                $this->redirect(route('afip-mi-simplificacion'));
            }


        } catch (ValidationException $e) {
            //Handle validation errors
            $this->dispatch('validationError', $e->errors());
        } catch (Exception $e) {
            //Handle file upload or other unexpected errors
            $this->dispatch('fileUploadError', $e->getMessage());
        }
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
