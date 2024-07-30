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
use Illuminate\Database\Eloquent\Collection;

class MapucheSicoss extends Component
{

    const TABLE_AFIP_IMPORT_CRUDO = 'AfipImportCrudo';
    const TABLE_AFIP_MAPUCHE_SICOSS = 'AfipMapucheSicoss';


    /* @var UploadedFile|null El archivo seleccionado actualmente
    *
    */
    public ?UploadedFile $selectedArchivo;
    /** @var Collection|null Listado de archivos subidos
     * 
     */
    public ?Collection $listadoArchivos;
    public ?int $selectedArchivoID = null;
    public ?string $filename = null;

    protected ?string $filepath = null;
    protected ?string $absolutePath = null;
    protected ?string $periodoFiscal = null;

    // protected $afipMapucheSicossTable;
    // protected $afipImportacionCrudaTable;


    protected $importService;
    protected $tableVerificationService;
    protected $workflowService;


    public function boot(
        ImportService $importService,
        TableVerificationService $tableVerificationService,
        WorkflowService $workflowService
    ) {
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



    public function render()
    {
        return view('livewire.mapuche-sicoss');
    }
}
