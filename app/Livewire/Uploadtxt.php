<?php

namespace App\Livewire;

use Exception;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\Mapuche\Dh22;
use App\Models\UploadedFile;
use App\Models\OrigenesModel;
use Livewire\WithFileUploads;
use App\Services\UploadService;
use Illuminate\Support\Facades\DB;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use App\Services\FileProcessingService;
use App\Contracts\WorkflowServiceInterface;
use App\Contracts\OrigenRepositoryInterface;
use Illuminate\Validation\ValidationException;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Contracts\FileUploadRepositoryInterface;


class Uploadtxt extends Component implements HasForms
{
    use WithFileUploads;
    use InteractsWithForms;


    public $archivotxt;
    public $archivotxtAfip;
    public $archivotxtMapuche;
    public $headers = [];
    public $importaciones;
    public $archivoModel = [];
    public $file_path;
    public $periodo_fiscal;
    public $origenes = ['afip', 'mapuche'];
    public $selectedOrigen;
    public $showUploadForm = false;
    public $nextStepUrl = null;
    public $processId;
    public $showButtonProcessFiles = false;
    public int $selectedLiquidacion;

    protected $workflowService;
    protected $processLog;
    protected $currentStep;
    private $fileUploadRepository;
    private $origenRepository;
    private $fileUploadService;
    private $fileProcessingService;


    /**
     * Constructor del componente.
     *
     * @param WorkflowServiceInterface $workflowService
     * @param FileUploadRepositoryInterface $fileUploadRepository
     * @param OrigenRepositoryInterface $origenRepository
     * @param FileUploadService $fileUploadService
     * @param FileProcessingService $file
     *
     */
    public function boot(
        WorkflowServiceInterface $workflowService,
        FileUploadRepositoryInterface $fileUploadRepository,
        OrigenRepositoryInterface $origenRepository,
        FileUploadService $fileUploadService,
        FileProcessingService $file,
    ) {
        $this->workflowService = $workflowService;
        $this->fileUploadRepository = $fileUploadRepository;
        $this->origenRepository = $origenRepository;
        $this->fileUploadService = $fileUploadService;
        $this->fileProcessingService = $file;

        $this->checkCurrentStep();
    }

    public function mount()
    {
        $this->importaciones = $this->fileUploadRepository->all();
        $this->origenes = OrigenesModel::all();
        $this->processId = (string)Str::uuid(); // generar un ID de proceso único y convertirlo a string
        Log::info("Process ID: {$this->processId}");
        Log::debug('Componente Uploadtxt montado');
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
        $this->checkShowButtonProcessFiles();
    }

    public function saveAfip()
    {
        $this->save('afip');
    }

    public function saveMapuche()
    {
        $this->save('mapuche');
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
    public function save($origen): void
    {
        try {
            $this->validateAndPrepare($origen);

            // Verifica el orden de subidaa de archivos
            if ($origen == 'mapuche' && !$this->fileUploadRepository->existsByOrigen('afip')) {
                throw new Exception('Primero debe cargar el archivo de AFIP');
            }

            // 1. Cargar el archivo en el servidor
            $filePath = $this->fileUploadService->uploadFile(
                $origen === 'afip' ? $this->archivotxtAfip : $this->archivotxtMapuche, 'afiptxt'
            );

            if (!$filePath) {
                throw new Exception('Error al cargar el archivo en el servidor.');
            }


            // 2. Almacenar en la base de datos del modelo UploadedFile

            $origenModel = $this->origenRepository->findByName($origen);
            if (!$origenModel) {
                throw new Exception("No se encontró el origen '{$origen}'.");
            }

            $uploadedFile = $this->fileUploadRepository->create([
                'filename' => basename($filePath),
                'original_name' => $origen === 'afip' ? $this->archivotxtAfip->getClientOriginalName() : $this->archivotxtMapuche->getClientOriginalName(),
                'file_path' => $filePath,
                'periodo_fiscal' => $this->periodo_fiscal,
                'origen' => $origenModel->name,
                'user_id' => 1,
                'user_name' => 'admin',
                'process_id' => $this->processId,
                'nro_liqui' => $this->selectedLiquidacion,
            ]);

            if (!$uploadedFile) {
                throw new Exception('Error al guardar la información del archivo en la base de datos.');
            }

            // 3. Actualizar el flujo de trabajo y redirigir
            $this->updateWorkflowAndRedirect($origen);

            if($this->fileUploadRepository->existsByOrigen('afip'))
            {
                log::info("Archivo AFIP cargado correctamente");
            }
            elseif ($this->fileUploadRepository->existsByOrigen('mapuche'))
            {
                log::info("Archivo MAPACHE cargado correctamente");
            }

            // 4. Procesar los archivos si ambos han sido cargados
            if ($this->fileUploadRepository->existsByOrigen('afip') && $this->fileUploadRepository->existsByOrigen('mapuche')) {
                $fileAfip = UploadedFile::where('origen', 'afip')->latest()->first();
                $fileMapuche = UploadedFile::where('origen', 'mapuche')->latest()->first();
                Log::info("Ambos archivos han sido cargados. Iniciando procesamiento...");

                $this->showButtonProcessFiles = true;

                if ($fileAfip->process_id == $fileMapuche->process_id){
                    Log::info('UUID iguales, redirigiendo');
                    $this->handleNextStep(); // Redirige solo si ambos archivos han sido cargados
                }
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Valida y prepara los datos de entrada antes de procesar el archivo.
     * Este método realiza las siguientes tareas:
     * 1. Valida los datos de entrada utilizando la función `validateInput()`.
     * 2. Obtiene el registro de flujo de trabajo más reciente utilizando `getLatestWorkflow()`.
     * 3. Obtiene el paso actual del flujo de trabajo utilizando `getCurrentStep()`.
     * 4. Registra información sobre el proceso y el paso actual en el registro.
     *
     * @return void
     */
    private function validateAndPrepare($origen)
    {
        Log::debug("uploadtxt->validateAndPrepare, Paso actual: {$origen}");

        // Validar la liquidación seleccionada
        $this->validate([
            'selectedLiquidacion' => 'required|exists:pgsql-mapuche.mapuche.dh22,nro_liqui',
            'archivotxt' . ucfirst($origen) => 'required|file|mimes:txt,csv|max:20480',
        ], [
            'selectedLiquidacion.required' => 'Debe seleccionar una liquidación',
            'selectedLiquidacion.exists' => 'La liquidación seleccionada no existe',
            // ... otros mensajes de validación ...
        ]);

        $this->processLog = $this->workflowService->getLatestWorkflow();
        $this->currentStep = $this->workflowService->getCurrentStep($this->processLog);
        Log::info("uploadtxt->validateAndPrepare, Paso actual: {$this->currentStep}");
    }

    public function processFiles(): void
    {
        Log::info("uploadtxt->processFiles");
        $this->fileProcessingService->processFiles();
    }

    public function checkShowButtonProcessFiles(): void
    {
        // Verificar si ambos archivos han sido subidos
        $afipFile = UploadedFile::where('origen', 'afip')->latest()->first();
        $mapucheFile = UploadedFile::where('origen', 'mapuche')->latest()->first();

        if ($afipFile && $mapucheFile && $afipFile->process_id == $mapucheFile->process_id) {
            $this->showButtonProcessFiles = true;
        } else {
            $this->showButtonProcessFiles = false;
        }
    }


    /**
     * Valida los datos de entrada antes de procesar el archivo.
     *
     * Esta función realiza las siguientes validaciones:
     * - Verifica que se haya seleccionado un archivo para subir.
     * - Verifica que el archivo sea de tipo .txt o .csv.
     * - Verifica que el archivo no supere los 20MB de tamaño.
     *
     * @param string $origen El origen del archivo a validar ('afip' o 'mapuche').
     * @return void
     */
    private function validateInput($origen): void
    {
        $messages = [
            'archivotxtAfip.required' => 'Por favor, seleccione un archivo para subir.',
            'archivotxtAfip.mimes' => 'Solo se permiten archivos de tipo .txt.',
            'archivotxtAfip.max' => 'El archivo no debe superar los 20MB.',
            'archivotxtMapuche.required' => 'Por favor, seleccione un archivo para subir.',
            'archivotxtMapuche.mimes' => 'Solo se permiten archivos de tipo .txt.',
            'archivotxtMapuche.max' => 'El archivo no debe superar los 20MB.',
        ];

        $this->validate([
            'archivotxt' . ucfirst($origen) => 'required|file|mimes:txt,csv|max:20480',
        ], $messages);
    }

    /**
     * Actualiza el paso actual del flujo de trabajo, restablece el formulario y maneja el siguiente paso.
     *
     * Esta función se encarga de completar el paso actual del flujo de trabajo, restablecer los campos del formulario a sus valores predeterminados y redirigir al usuario al siguiente paso del flujo de trabajo, si lo hay.
     * @param string $name
     * @return void
     */
    private function updateWorkflowAndRedirect($origen)
    {
        $this->updateWorkflowStep($origen);
        // $this->resetForm($origen);
        // $this->handleNextStep();
    }

    /**
     * Restablece los campos del formulario a sus valores predeterminados.
     *
     * Esta función se encarga de restablecer los campos del formulario, como el archivo cargado, el período fiscal y la ruta del archivo, y también restablece la validación del formulario.
     * @param string $name
     * @return void
     */
    private function resetForm($origen)
    {
        switch ($origen) {
            case 'afip':
                $this->archivotxt = null;
                $this->periodo_fiscal = '';
                $this->file_path = '';
                break;
            default:
                $this->archivotxt = null;
                $this->periodo_fiscal = '';
                $this->file_path = '';
                break;
        }
        $this->resetValidation();
    }

    /**
     * Actualiza el paso actual del flujo de trabajo.
     *
     * Esta función se encarga de completar el paso actual del flujo de trabajo, ya sea 'subir_archivo_afip' o 'subir_archivo_mapuche', según el paso actual.
     * Después de completar el paso, se registra un mensaje informativo en el log de la aplicación.
     *
     * @return void
     */
    private function updateWorkflowStep($origen)
    {
        $stepToComplete = $origen == 'afip' ? 'subir_archivo_afip' : 'subir_archivo_mapuche';
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




    public function deleteFile($fileId)
    {
        try {
            DB::transaction(function () use ($fileId) {
                $file = $this->fileUploadRepository->findOrFail($fileId);
                $this->deleteFileAndRecord($file);
            });
            $this->handleSuccessfulDeletion();
        } catch (Exception $e) {
            $this->dispatch('error', 'Error: ' . $e->getMessage());
        }
    }

    private function deleteFileAndRecord($file)
    {
        if (!$this->fileUploadService->deleteFile($file->file_path)) {
            throw new Exception('Error al eliminar el archivo del servidor.');
        }
        $this->fileUploadRepository->delete($file);
    }

    private function handleSuccessfulDeletion()
    {
        $this->dispatch('success', 'Archivo eliminado correctamente.');
        $this->dispatch('fileDeleted');
        $this->importaciones = $this->fileUploadRepository->all();
    }
    public function updatedImportaciones()
    {
        //
    }

    protected function getFormSchema(): array
    {
        $options = Dh22::query()
            ->definitiva()
            ->orderBy('nro_liqui', 'desc')
            ->limit(12)
            ->get();

        Log::debug('Liquidaciones encontradas:', [
            'count' => $options->count(),
            'liquidaciones' => $options->map(fn($liq) => [
                'nro_liqui' => $liq->nro_liqui,
                'desc_liqui' => $liq->desc_liqui
            ])->toArray()
        ]);

        return [
            Select::make('selectedLiquidacion')
                ->label('Liquidación')
                ->options(function () use ($options) {
                    $mappedOptions = $options->mapWithKeys(function ($liquidacion) {
                        $option = "#{$liquidacion->nro_liqui} - {$liquidacion->desc_liqui}";
                        Log::debug("Opción generada:", [
                            'nro_liqui' => $liquidacion->nro_liqui,
                            'option' => $option
                        ]);
                        return [$liquidacion->nro_liqui => $option];
                    })->toArray();

                    Log::debug('Opciones finales del selector:', $mappedOptions);
                    return $mappedOptions;
                })
                ->searchable()
                ->preload()
                ->required()
                ->placeholder('Seleccione una liquidación')
                ->helperText('Seleccione la liquidación definitiva correspondiente')
                ->columnSpanFull()
                ->afterStateUpdated(function ($state) {
                    Log::debug('Liquidación seleccionada:', ['selectedLiquidacion' => $state]);
                })
        ];
    }

    public function render()
    {
        Log::debug('Renderizando componente Uploadtxt', [
            'selectedLiquidacion' => $this->selectedLiquidacion ?? 'no seleccionada'
        ]);
        if ( $this->showUploadForm) {
            return view('livewire.uploadtxt');
        } else {
            return view('livewire.uploadtxtcompleted', [
                'redirectUrl' => $this->nextStepUrl,
            ]);
        }
    }
}
