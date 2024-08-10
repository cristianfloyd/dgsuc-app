<?php

namespace App\Livewire;

use App\Contracts\FileProcessorInterface;
use App\ImportService;
use Livewire\Component;
use App\Models\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Contracts\WorkflowServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\TableManagementServiceInterface;
use App\Services\ColumnMetadata;
use Illuminate\View\View;

/**
 * Componente Livewire que maneja la importación de archivos AFIP SICOSS y el flujo de trabajo correspondiente.
 */
class SicossImporter extends Component
{
    // Constantes de clase
    private const string STEP_IMPORT_ARCHIVO = 'import_archivo_mapuche';


    // Propiedades públicas
    public ?UploadedFile $selectedArchivo;
    public ?Collection $listadoArchivos;
    public ?int $selectedArchivoID = null;
    public ?string $filename = null;
    public ?string $nextstepUrl = null;
    public bool $showUploadForm = false;

    // Propiedades protegidas
    protected ?string $filepath = null;
    protected ?string $absolutePath = null;
    protected ?int $periodoFiscal = null;

    // Servicios inyectados
    private readonly ImportService $importService;
    private readonly WorkflowServiceInterface $workflowService;
    private readonly FileProcessorInterface $fileProcessor;


    public function boot(
        ImportService $importService,
        WorkflowServiceInterface $workflowService,
        FileProcessorInterface $fileProcessor,
    ) {
        $this->importService = $importService;
        $this->workflowService = $workflowService;
        $this->fileProcessor = $fileProcessor;
        $this->checkCurrentStep();
    }


    public function mount()
    {
        $this->listadoArchivos = UploadedFile::all();
    }


    /**
     * Verifica el paso actual del flujo de trabajo y actualiza las propiedades correspondientes.
     */
    public function checkCurrentStep(): void
    {
        $processLog = $this->workflowService->getLatestWorkflow();
        $currentStep = $this->workflowService->getCurrentStep($processLog);
        $this->showUploadForm = in_array($currentStep, [self::STEP_IMPORT_ARCHIVO]);
        $this->nextstepUrl = $this->workflowService->getStepUrl($currentStep);
    }

    /**
     * Importa un archivo y maneja el proceso después de una importación exitosa.
     *
     * @param int|null $archivoId
     * @return void
     */
    public function importarArchivo(?int $archivoId = null): void
    {
        try {
            $file = UploadedFile::findOrFail($archivoId ?? $this->selectedArchivoID);
            $processLog = $this->workflowService->getLatestWorkflow();

            // Utilizamos el nuevo método handleFileImport del FileProcessorService
            if ($this->fileProcessor->handleFileImport($file, $this->getColumnWidths())) {
                $this->workflowService->completeStep($processLog, self::STEP_IMPORT_ARCHIVO);
                $nextStep = $this->workflowService->getNextStep(self::STEP_IMPORT_ARCHIVO);
                $this->dispatch('success', 'Importación completada con éxito.');
                $this->dispatch('paso-completado', ['nextStep' => $nextStep]);
            } else {
                $this->dispatch('error', 'Hubo un problema durante la importación.');
            }
        } catch (\Exception $e) {
            $this->dispatch('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }


    public function seleccionarArchivo()
    {
        $this->selectedArchivo = UploadedFile::find($this->selectedArchivoID);
        $this->filename = $this->selectedArchivo->filename;
        $this->periodoFiscal = $this->selectedArchivo->periodo_fiscal;
        $this->filepath = $this->selectedArchivo->filepath;
        $this->absolutePath = $this->selectedArchivo->absolute_path;
    }


    private function getColumnWidths(): array
    {
        $data = new ColumnMetadata;
        return $data->getWidths();
    }


    public function render(): View
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
