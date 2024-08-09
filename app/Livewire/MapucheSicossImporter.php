<?php

namespace App\Livewire;

use App\ImportService;
use Livewire\Component;
use App\Models\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Contracts\WorkflowServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\TableManagementServiceInterface;

/**
 * Componente Livewire que maneja la importación de archivos AFIP SICOSS y el flujo de trabajo correspondiente.
 *
 * Este componente se encarga de:
 * - Mostrar el formulario de carga de archivos cuando corresponde según el flujo de trabajo actual.
 * - Permitir la selección de un archivo previamente cargado.
 * - Importar el archivo seleccionado y completar los pasos del flujo de trabajo.
 * - Actualizar las propiedades del componente con la información del archivo seleccionado.
 * - Redirigir al usuario al siguiente paso del flujo de trabajo una vez completada la importación.
 */
class MapucheSicossImporter extends Component
{

    /** @var UploadedFile|null El archivo seleccionado actualmente
    *
    */
    public ?UploadedFile $selectedArchivo;
    /** @var Collection|null Listado de archivos subidos
     *
     */
    public ?Collection $listadoArchivos;
    public ?int $selectedArchivoID = null;
    public ?string $filename = null;
    public ?string $nextstepUrl = null;
    public ?string $showUploadForm = null;
    protected ?string $filepath = null;
    protected ?string $absolutePath = null;
    protected ?string $periodoFiscal = null;


    protected $importService;
    protected $tableManagementService;
    protected $workflowService;


    public function boot(
        ImportService $importService,
        TableManagementServiceInterface $tableManagementService,
        WorkflowServiceInterface $workflowService
    ) {
        $this->importService = $importService;
        $this->tableManagementService = $tableManagementService;
        $this->workflowService = $workflowService;
        $this->checkCurrentStep();
    }


    public function mount()
    {
        $this->listadoArchivos = UploadedFile::all();
    }

    public function checkCurrentStep()
    {
        $processLog = $this->workflowService->getLatestWorkflow();
        $currentStep = $this->workflowService->getCurrentStep($processLog);
        $this->showUploadForm = in_array($currentStep, ['import_archivo_mapuche']);
        $this->nextstepUrl = $this->workflowService->getStepUrl($currentStep);
    }

    /**
     * Importa un archivo de AFIP SICOSS y completa los pasos del flujo de trabajo correspondiente.
     *
     * @param int|null $archivoId El ID del archivo a importar. Si no se proporciona, se utilizará el ID del archivo seleccionado actualmente.
     * @return void
     */
    public function importarArchivo($archivoId = null): void
    {
        $archivoId ??= $this->selectedArchivoID;


        $file = UploadedFile::findOrFail($archivoId);

        if ($file) {
            $this->dispatch('success', message: 'Archivo Encontrado');
            //obtener el ultimo workflow
            $latestWorkflow = $this->workflowService->getLatestWorkflow();
            $currentStep = $this->workflowService->getCurrentStep($latestWorkflow);
        }

        $resultado = $this->importService->importFile($file);

        if ($resultado) {
            $this->workflowService->completeStep($latestWorkflow, 'import_archivo_mapuche');
            $nextStep = $this->workflowService->getNextStep($currentStep);
            $this->dispatch('success', 'Importación completada con éxito.');
            $this->dispatch('paso-completado', ['nextStep' => $nextStep]);
            $this->nextstepUrl = $this->workflowService->getStepUrl($nextStep);

        } else {
            $this->dispatch('error', 'Hubo un problema durante la importación.');
        }
    }

    public function seleccionarArchivo(): void
    {
        //
    }


    /**
     * Actualiza la propiedad `$selectedArchivo` con el archivo seleccionado, y establece las propiedades `$filepath`, `$absolutePath`, `$periodoFiscal` y `$filename` con los valores correspondientes del archivo.
     *
     * @param int $archivoId El ID del archivo seleccionado.
     * @return void
     */
    public function updatedSelectedArchivoID($archivoId): void
    {
        $this->selectedArchivo = UploadedFile::findOrFail($archivoId);
        $this->filepath = $this->selectedArchivo->file_path;
        $this->absolutePath =  storage::path($this->filepath);
        $this->periodoFiscal = $this->selectedArchivo->periodo_fiscal;
        $this->filename = $this->selectedArchivo->original_name;
    }



    public function render()
    {
        if (!$this->showUploadForm) {
            return view('livewire.mapuche-sicoss');
        } else {
            return view('livewire.uploadtxtcompleted',[
                'redirectUrl' => $this->nextstepUrl,
            ]);
        }
    }
}
