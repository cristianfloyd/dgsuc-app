<?php

namespace App\Services;

use App\Models\UploadedFile;
use App\Livewire\CompareCuils;
use App\Livewire\SicossImporter;
use Illuminate\Support\Facades\Log;
use App\Livewire\AfipRelacionesActivas;
use App\Contracts\FileProcessorInterface;
use App\Contracts\EmployeeServiceInterface;
use App\Contracts\WorkflowServiceInterface;
use App\Jobs\ImportAfipRelacionesActivasJob;
use App\Contracts\TransactionServiceInterface;
use App\Contracts\FileUploadRepositoryInterface;

class FileProcessingService
{
    private $afipRelacionesActivas;
    private $sicossImporter;
    private $compareCuils;
    private $fileUploadRepository;
    private $fileProcessor;
    private $employeeService;
    private $validationService;
    private $transactionService;
    private $workflowService;
    private $columnMetadata;
    private $sicossImporterService;

    public function __construct(
        AfipRelacionesActivas $afipRelacionesActivas,
        SicossImporter $sicossImporter,
        CompareCuils $compareCuils,
        FileUploadRepositoryInterface $fileUploadRepository,
        FileProcessorInterface $fileProcessor,
        EmployeeServiceInterface $employeeService,
        ValidationService $validationService,
        TransactionServiceInterface $transactionService,
        WorkflowServiceInterface $workflowService,
        ColumnMetadata $columnMetadata,
        SicossImportService $sicossImporterService,
    ){
        $this->afipRelacionesActivas = $afipRelacionesActivas;
        $this->sicossImporter = $sicossImporter;
        $this->compareCuils = $compareCuils;
        $this->fileUploadRepository = $fileUploadRepository;
        $this->fileProcessor = $fileProcessor;
        $this->employeeService = $employeeService;
        $this->validationService = $validationService;
        $this->transactionService = $transactionService;
        $this->workflowService = $workflowService;
        $this->columnMetadata = $columnMetadata;
        $this->sicossImporterService = $sicossImporterService;
    }

    public function processFiles()
    {
        // Verificar si ambos archivos han sido subidos
        $afipFile = UploadedFile::where('origen', 'afip')->latest()->first();
        $mapucheFile = UploadedFile::where('origen', 'mapuche')->latest()->first();


        // Verificar que ambos archivos tienen el mismo UUID
        if ($afipFile->process_id !== $mapucheFile->process_id) {
            Log::error('Los archivos no tienen el mismo UUID.');
            return;
        }


        if($afipFile && $mapucheFile)
        {
            // Procesar e importar el archivo AFIP
            $this->processFileAfip($afipFile);


            // Procesar e importar el archivo Mapuche
            $this->processFileMapuche($mapucheFile);


            Log::info('Archivos procesados e importados correctamente.');

            // Ejecutar el paso de comparación de CUILs
            $this->executeCompareCuilsStep();

            // Eliminar ambos archivos
            // $this->deleteFiles($afipFile, $mapucheFile);
        } else {
            Log::error('No se han subido ambos archivos.');
        }
    }

    private function processFileAfip($afipFile)
    {
            $uploadedFileId = $afipFile->id;

            // Despachar el Job
            ImportAfipRelacionesActivasJob::dispatch(
                $this->fileProcessor,
                $this->employeeService ,
                $this->validationService ,
                $this->transactionService,
                $this->workflowService,
                $this->columnMetadata,
                $uploadedFileId
            );
        Log::info('ImportAfipRelacionesActivasJob disparado correctamente.');
    }

    private function processFileMapuche($mapucheFile)
    {
        // aca se va a procesar el archivo mapuche
        $tableName = 'suc.afip_mapuche_sicoss';
        $step = 'import_archivo_mapuche';

        if ($this->sicossImporterService->importarArchivo($mapucheFile, $tableName, $step)) {
            Log::info('Archivo Mapuche procesado e importado correctamente.');
        } else {
            Log::error('Error al procesar el archivo Mapuche.');
        }

    }

    private function executeCompareCuilsStep()
    {
        // try {
        //     $this->compareCuils->excecuteWorkfloSteps();
        //     Log::info('Paso de comparación de CUILs ejecutado correctamente.');
        // } catch (\Exception $e) {
        //     Log::error('Error al ejecutar el paso de comparación de CUILs: ' . $e->getMessage());
        // }
    }

    private function deleteFiles($afipFile, $mapucheFile)
    {
        try {
            // Utilizar el repositorio para eliminar los archivos
            $this->fileUploadRepository->delete($afipFile);
            $this->fileUploadRepository->delete($mapucheFile);

            Log::info('Archivos eliminados correctamente de la base de datos y del servidor.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar los archivos: ' . $e->getMessage());
        }
    }
}

