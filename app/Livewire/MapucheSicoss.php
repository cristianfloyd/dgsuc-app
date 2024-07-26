<?php
namespace App\Livewire;

use App\ImportService;
use Livewire\Component;
use App\Models\UploadedFile;
use App\Models\AfipMapucheSicoss;
use App\TableVerificationService;
use App\Models\AfipSicossDesdeMapuche;
use Illuminate\Support\Facades\Storage;
use App\Models\AfipImportacionCrudaModel;
use App\Services\WorkflowService;

class MapucheSicoss extends Component
{

    const TABLE_AFIP_IMPORT_CRUDO = 'AfipImportCrudo';
    const TABLE_AFIP_MAPUCHE_SICOSS = 'AfipMapucheSicoss';

    public ?UploadedFile $selectedArchivo;
    public $listadoArchivos;
    public ?int $selectedArchivoID;
    public ?string $filename;

    protected ?string $filepath;
    protected ?string $absolutePath;
    protected ?string $periodoFiscal;

    protected $afipMapucheSicossTable;
    protected $afipImportacionCrudaTable;
    protected $importService;
    protected $tableVerificationService;
    protected $workflowService;


    public function boot(ImportService $importService,
            TableVerificationService $tableVerificationService,
        WorkflowService $workflowService
    ){
        $this->importService = $importService;
        $this->tableVerificationService = $tableVerificationService;
        $this->workflowService = $workflowService;
    }


    public function mount()
    {
        $this->listadoArchivos = UploadedFile::all();
    }

    public function importarArchivo($archivoId = null): void
    {
        if ($archivoId === null) {
            $archivoId = $this->selectedArchivoID;
        }

        $archivo = UploadedFile::findOrFail($archivoId);

        if($archivo){
            $this->dispatch('success', message: 'Archivo Encontrado');
            //obtener el ultimo workflow
            $latestWorkflow = $this->workflowService->getLatestWorkflow();
        }

        $resultado = $this->importService->importFile($archivo);

        if ($resultado) {
            //completar el workflow
            $this->workflowService->completeStep($latestWorkflow, 'import_archivo_mapuche');

            //obtener el siguiente paso
            $nextStep = $this->workflowService->getNextStep($latestWorkflow);

            if ($nextStep) {
                //ir al siguiente paso
                $nextStepUrl = $this->workflowService->getStepUrl($nextStep);
                $this->dispatch('success', 'Importación completada con éxito.');
                redirect()->to($nextStepUrl);
            } else {
                $this->dispatch('success', 'Importación completada con éxito. Proceso finalizado.');
            }
        } else {
            $this->dispatch('error', 'Hubo un problema durante la importación.');
        }
    }

    public function seleccionarArchivo()
    {
        //
    }
    public function updatedSelectedArchivoID($archivoId): void
    {
        $this->selectedArchivo = UploadedFile::findOrFail($archivoId);
        $this->filepath = $this->selectedArchivo->file_path;
        $this->absolutePath =  storage::path($this->filepath);
        $this->periodoFiscal = $this->selectedArchivo->periodo_fiscal;
        $this->filename = $this->selectedArchivo->original_name;
    }


    /**
     * Verifica las tablas necesarias para el funcionamiento del componente.
     */
    public function verificarTablas(): void
    {
        $this->verifyAfipImportCrudoTable();
        $this->verifyAfipMapucheSicossTable();
    }

    public function verifyAfipImportCrudoTable(): void
    {
        $this->afipImportacionCrudaTable = $this->tableVerificationService->verifyTableIsEmpty(new AfipImportacionCrudaModel, self::TABLE_AFIP_IMPORT_CRUDO);
        $this->emitTableVerificationResult(self::TABLE_AFIP_IMPORT_CRUDO, $this->afipImportacionCrudaTable);
    }

    public function verifyAfipMapucheSicossTable(): void
    {
        $this->afipMapucheSicossTable = $this->tableVerificationService->verifyTableIsEmpty(new AfipMapucheSicoss, self::TABLE_AFIP_MAPUCHE_SICOSS);
        $this->emitTableVerificationResult(self::TABLE_AFIP_MAPUCHE_SICOSS, $this->afipMapucheSicossTable);
    }
    private function emitTableVerificationResult(string $tableName, bool $isNotEmpty): void
    {
        if($isNotEmpty){
            $this->dispatch('success', "La tabla $tableName no está vacía.");
        } else {
            $this->dispatch('error', "La tabla $tableName no está vacía.");
        }
    }


    public function render()
    {
        return view('livewire.mapuche-sicoss');
    }
}
