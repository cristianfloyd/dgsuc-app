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
use App\Contracts\WorkflowExecutionInterface;

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
    private $workflowExecutionService;

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
        WorkflowExecutionInterface $workflowExecutionService,
    ) {
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
        $this->workflowExecutionService = $workflowExecutionService;
    }

    public function processFiles()
    {
        // Verificar si ambos archivos han sido subidos
        $afipFile = UploadedFile::where('origen', 'afip')->latest()->first();
        $mapucheFile = UploadedFile::where('origen', 'mapuche')->latest()->first();


        // Verificar que ambos archivos tienen el mismo UUID
        if (!$afipFile || !$mapucheFile || $afipFile->process_id !== $mapucheFile->process_id) {
            return [
                'success' => false,
                'message' => 'Los archivos no están disponibles o no coinciden.',
                'data' => []
            ];
        }
        // if ($afipFile->process_id !== $mapucheFile->process_id) {
        //     Log::error('Los archivos no tienen el mismo UUID.');
        //     return;
        // }

        $result = [
            'success' => true,
            'message' => 'Procesamiento completado',
            'data' => [
                'afip' => [],
                'mapuche' => [],
                'workflow' => []
            ]
        ];

        // Procesar archivo AFIP
        $afipResult = $this->processFileAfip($afipFile);
        $result['data']['afip'] = $afipResult;
        if (!$afipResult['success']) {
            $result['success'] = false;
            $result['message'] = 'Error en el procesamiento del archivo AFIP';
            return $result;
        }

        // Procesar archivo Mapuche
        $mapucheResult = $this->processFileMapuche($mapucheFile);
        $result['data']['mapuche'] = $mapucheResult;
        if (!$mapucheResult['success']) {
            $result['success'] = false;
            $result['message'] = 'Error en el procesamiento del archivo Mapuche';
            return $result;
        }

        // Ejecutar workflow
        try {
            $workflowResult = $this->workflowExecutionService
                ->setPerPage(10)
                ->setPeriodoFiscal($afipFile->periodo_fiscal)
                ->setNroLiqui(1)
                ->executeWorkflowSteps();
            $result['data']['workflow'] = $workflowResult;
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = 'Error en la ejecución del workflow: ' . $e->getMessage();
            $result['data']['workflow'] = ['error' => $e->getMessage()];
        }

        return $result;


        // if ($afipFile && $mapucheFile) {
        //     // Procesar e importar el archivo AFIP
        //     $this->processFileAfip($afipFile);
        //
        //
        //     // Procesar e importar el archivo Mapuche
        //     $this->processFileMapuche($mapucheFile);
        //
        //
        //     Log::info('Archivos procesados e importados correctamente.');
        //
        //     // Ejecutar el paso de comparación de CUILs
        //     // $this->executeCompareCuilsStep();
        //     $this->workflowExecutionService
        //         ->setPerPage(10)
        //         ->setPeriodoFiscal($afipFile->periodo_fiscal)
        //         ->setNroLiqui(1)
        //         ->executeWorkflowSteps();
        //
        //     // Eliminar ambos archivos
        //     // $this->deleteFiles($afipFile, $mapucheFile); ese solo elimina de la base de datos, no del disco
        // } else {
        //     Log::error('No se han subido ambos archivos.');
        // }
    }

    /**
     * Procesa el archivo AFIP y ejecuta el trabajo de importación de relaciones activas.
     *
     * @param UploadedFile $afipFile El archivo AFIP a procesar.
     * @return array El resultado de la ejecución del trabajo de importación.
     */
    private function processFileAfip($afipFile): array
    {
        $uploadedFileId = $afipFile->id;

        $result = ImportAfipRelacionesActivasJob::dispatchSync(
            $this->fileProcessor,
            $this->employeeService,
            $this->validationService,
            $this->transactionService,
            $this->workflowService,
            $this->columnMetadata,
            $uploadedFileId
        );

        if ($result['success']) {
            Log::info('ImportAfipRelacionesActivasJob completado exitosamente. ' . $result['message']);
            Log::info('Líneas procesadas: ' . $result['data']['linesProcessed']);
        } else {
            Log::error('Error en ImportAfipRelacionesActivasJob: ' . $result['message']);
            if (isset($result['error'])) {
                Log::error('Detalles del error: ' . $result['error']);
            }
        }

        return $result;
    }


    private function processFileMapuche($mapucheFile)
    {
        // aca se va a procesar el archivo mapuche
        $tableName = 'suc.afip_mapuche_sicoss';
        $step = 'import_archivo_mapuche';

        try {
            $result = $this->sicossImporterService->importarArchivo($mapucheFile, $tableName, $step);

            if ($result) {
                Log::info('Archivo Mapuche procesado e importado correctamente.');
                return [
                    'success' => true,
                    'message' => 'Archivo Mapuche procesado e importado correctamente.',
                    'data' => [
                        'tableName' => $tableName,
                        'step' => $step,
                        'fileId' => $mapucheFile->id
                    ]
                ];
            } else {
                Log::error('Error al procesar el archivo Mapuche.');
                return [
                    'success' => false,
                    'message' => 'Error al procesar el archivo Mapuche.',
                    'data' => [
                        'tableName' => $tableName,
                        'step' => $step,
                        'fileId' => $mapucheFile->id
                    ]
                ];
            }
        } catch (\Exception $e) {
            Log::error('Excepción al procesar el archivo Mapuche: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Excepción al procesar el archivo Mapuche: ' . $e->getMessage(),
                'data' => [
                    'tableName' => $tableName,
                    'step' => $step,
                    'fileId' => $mapucheFile->id,
                    'error' => $e->getMessage()
                ]
            ];
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
