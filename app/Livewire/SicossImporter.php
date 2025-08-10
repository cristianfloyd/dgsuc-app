<?php

namespace App\Livewire;

use App\Contracts\FileProcessorInterface;
use App\Contracts\TableManagementServiceInterface;
use App\Contracts\WorkflowServiceInterface;
use App\ImportService;
use App\Models\UploadedFile;
use App\Services\ColumnMetadata;
use app\Services\DatabaseService;
use App\Services\SicossImportService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Componente Livewire que maneja la importación de archivos AFIP SICOSS y el flujo de trabajo correspondiente.
 */
class SicossImporter extends Component
{
    // Constantes de clase
    private const  STEP_IMPORT_ARCHIVO = 'import_archivo_mapuche';
    private const  TABLE_NAME = 'suc.afip_mapuche_sicoss';

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
    protected readonly ImportService $importService;

    protected SicossImportService $sicossImporterService;

    private readonly WorkflowServiceInterface $workflowService;

    private readonly FileProcessorInterface $fileProcessor;

    private readonly TableManagementServiceInterface $tableManagementService;

    private readonly DatabaseService $databaseService;

    public function boot(
        ImportService $importService,
        WorkflowServiceInterface $workflowService,
        FileProcessorInterface $fileProcessor,
        TableManagementServiceInterface $tableManagementService,
        DatabaseService $databaseService,
        SicossImportService $sicossImporterService,
    ): void {
        $this->importService = $importService;
        $this->workflowService = $workflowService;
        $this->fileProcessor = $fileProcessor;
        $this->tableManagementService = $tableManagementService;
        $this->databaseService = $databaseService;
        $this->sicossImporterService = $sicossImporterService;
        $this->checkCurrentStep();
    }

    /**
     * Verifica el paso actual del flujo de trabajo y actualiza las propiedades correspondientes.
     */
    public function checkCurrentStep(): void
    {
        $processLog = $this->workflowService->getLatestWorkflow();
        $currentStep = $this->workflowService->getCurrentStep($processLog);
        $this->showUploadForm = $currentStep == self::STEP_IMPORT_ARCHIVO;
        $this->nextstepUrl = $this->workflowService->getStepUrl($currentStep);
    }

    public function mount(): void
    {
        $this->listadoArchivos = UploadedFile::all();
    }

    public function importar(?int $archivoId = null): void
    {
        $file = UploadedFile::findOrFail($archivoId ?? $this->selectedArchivoID);
        if ($this->sicossImportService->importarArchivo($file)) {
            $this->dispatch('success', 'Importación completada con éxito.');
        } else {
            $this->dispatch('error', 'Hubo un problema durante la importación.');
        }
    }

    /**
     * Importa un archivo y maneja el proceso después de una importación exitosa.
     *
     * @param int|null $archivoId
     *
     * @return void
     */
    public function importarArchivo(?int $archivoId = null): void
    {
        try {
            $file = UploadedFile::findOrFail($archivoId ?? $this->selectedArchivoID);
            $processLog = $this->workflowService->getLatestWorkflow();
            $system = $file->origen;

            // Paso 1: Procesar el archivo y obtener los datos mapeados
            $mappedData = $this->fileProcessor->handleFileImport($file, $system);
            Log::info('Datos mapeados:', [$mappedData->count()]);


            if ($mappedData->isNotEmpty()) {
                // Paso 2: Verificar y preparar la tabla
                $tableName = self::TABLE_NAME;
                Log::info('Verificando y preparando tabla:', [$tableName]);
                $this->verifyAndPrepareTable($tableName);
                Log::info('Tabla verificada y preparada:', [$tableName]);

                // Paso 3: Insertar los datos mapeados en la base de datos
                $inserted = $this->databaseService->insertBulkData($mappedData, $tableName);

                if ($inserted) {
                    // Actualizar el flujo de trabajo y notificar al usuario
                    $this->workflowService->completeStep($processLog, self::STEP_IMPORT_ARCHIVO);
                    $nextStep = $this->workflowService->getNextStep(self::STEP_IMPORT_ARCHIVO);
                    $this->dispatch('success', 'Importación completada con éxito.');
                    $this->dispatch('paso-completado', ['nextStep' => $nextStep]);
                } else {
                    $this->dispatch('error', 'Hubo un problema durante la inserción de datos.');
                }
            } else {
                $this->dispatch('error', 'Hubo un problema durante la importación.');
            }
        } catch (\Exception $e) {
            $this->dispatch('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    public function seleccionarArchivo(): void
    {
        $this->selectedArchivo = $this->getArchivoById($this->selectedArchivoID);
        $this->filename = $this->selectedArchivo->filename;
        $this->periodoFiscal = $this->selectedArchivo->periodo_fiscal;
        $this->filepath = $this->selectedArchivo->filepath;
        $this->absolutePath = $this->selectedArchivo->absolute_path;
    }

    public function render(): View
    {
        if ($this->showUploadForm) {
            return view('livewire.mapuche-sicoss');
        }
        return view('livewire.uploadtxtcompleted', [
            'redirectUrl' => $this->nextstepUrl,
        ]);
    }

    /**
     * Set the value of selectedArchivo.
     *
     * @param $selectedArchivo
     *
     * @return self
     */
    public function setSelectedArchivo($selectedArchivo): static
    {
        $this->selectedArchivo = $selectedArchivo;
        return $this;
    }

    private function verifyAndPrepareTable($tableName): void
    {
        if ($this->tableManagementService::verifyAndPrepareTable($tableName)) {
            Log::info('Tabla verificada y preparada:', [$tableName]);
        } else {
            Log::info('Error al verificar y preparar la tabla:', [$tableName]);
        }
    }

    private function getArchivoById(int $archivoId): UploadedFile
    {
        return UploadedFile::find($archivoId);
    }

    private function getColumnWidths(): array
    {
        $data = new ColumnMetadata();
        return $data->getWidths();
    }
}
