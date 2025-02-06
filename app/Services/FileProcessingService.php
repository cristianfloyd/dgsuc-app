<?php

namespace App\Services;

use App\Models\UploadedFile;
use App\Livewire\CompareCuils;
use App\Livewire\SicossImporter;
use Illuminate\Support\Facades\Log;
use App\Livewire\AfipRelacionesActivas;
use App\Contracts\FileProcessorInterface;
use App\Contracts\DatabaseServiceInterface;
use App\Contracts\EmployeeServiceInterface;
use App\Contracts\WorkflowServiceInterface;
use App\Jobs\ImportAfipRelacionesActivasJob;
use App\Contracts\WorkflowExecutionInterface;
use App\Contracts\TransactionServiceInterface;
use App\Contracts\FileUploadRepositoryInterface;
use App\Contracts\TableManagementServiceInterface;

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
    private $databaseService;
    private $tableManagementService;

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
        DatabaseServiceInterface $databaseService,
        TableManagementServiceInterface $tableManagementService,
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
        $this->databaseService = $databaseService;
        $this->tableManagementService = $tableManagementService;
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


        $result = [
            'success' => true,
            'message' => 'Procesamiento completado',
            'data' => [
                'afip' => [],
                'mapuche' => [],
                'workflow' => []
            ]
        ];

        // 1.- Procesar archivo AFIP
        $afipResult = $this->processFileAfip($afipFile);

        $result['data']['afip'] = $afipResult;

        if (!$afipResult['success']) {
            $result['success'] = false;
            $result['message'] = 'Error en el procesamiento del archivo AFIP';
            return $result;
        }

        // 2.- Procesar archivo Mapuche
        $mapucheResult = $this->processFileMapuche($mapucheFile);

        $result['data']['mapuche'] = $mapucheResult;

        if (!$mapucheResult['success']) {
            $result['success'] = false;
            $result['message'] = 'Error en el procesamiento del archivo Mapuche';
            return $result;
        }

        // 3.- Ejecutar workflow
        try {
            $workflowResult = $this->workflowExecutionService
                ->setPerPage(10)
                ->setPeriodoFiscal($afipFile->periodo_fiscal)
                ->setNroLiqui($afipFile->nro_liqui)
                ->executeWorkflowSteps();
            $result['data']['workflow'] = $workflowResult;
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = 'Error en la ejecución del workflow: ' . $e->getMessage();
            $result['data']['workflow'] = ['error' => $e->getMessage()];
        }
        Log::info('Resultado del procesamiento fileProcessingService:', $result);
        return $result;
    }


    /**
     * Procesa un archivo AFIP y lo importa a la base de datos.
     *
     * Este método realiza los siguientes pasos:
     * 1. Procesa el archivo y mapea los datos
     * 2. Verifica y prepara la tabla destino
     * 3. Inserta los datos mapeados en la base de datos
     *
     * @param UploadedFile $afipFile El archivo AFIP a procesar
     * @return array Resultado del procesamiento con la siguiente estructura:
     *               - success: bool Indica si el proceso fue exitoso
     *               - message: string Mensaje descriptivo del resultado
     *               - data: array Datos adicionales del proceso
     *                 - file: int ID del archivo procesado
     *                 - tableName: string Nombre de la tabla destino
     *                 - step: string Paso actual del workflow
     *                 - recordsProcessed: int|null Cantidad de registros procesados (solo si success=true)
     *                 - error: string|null Mensaje de error (solo si success=false)
     */
    private function processFileAfip($afipFile): array
    {
        $uploadedFileId = $afipFile->id;
        $processLog = $this->workflowService->getLatestWorkflow();
        $step = $this->workflowService->getCurrentStep($processLog);
        $uploadedFile = UploadedFile::query()->findOrFail($uploadedFileId);
        $system = $uploadedFile->origen;
        $tableName = 'afip_relaciones_activas';

        // Paso 1: Procesar el archivo y obtener los datos mapeados
        $mappedData = $this->fileProcessor->handleFileImport($uploadedFile, $system);
        Log::info('Datos mapeados:', [$mappedData->count()]);



        if ($mappedData->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No se encontraron datos para procesar',
                'data' => ['file' => $uploadedFile->id, 'tableName' => $tableName, 'step' => $step]
            ];
        }

        // Paso 2: Verificar y preparar la tabla
        Log::info('Verificando y preparando tabla:', [$tableName]);
        $tableResult = $this->tableManagementService->verifyAndPrepareTable($tableName);

        if (!$tableResult['success']) {
            return [
                'success' => false,
                'message' => 'Error al verificar y preparar la tabla: ' . $tableResult['message'],
                'data' => array_merge(['file' => $uploadedFile->id, 'step' => $step], $tableResult['data']),
                'error' => $tableResult['error'] ?? null
            ];
        }

        Log::info('Tabla verificada y preparada:', $tableResult['actions']);

        // Paso 3: Insertar los datos mapeados en la base de datos
        $inserted = $this->databaseService->insertBulkData($mappedData, $tableName);

        if ($inserted) {
            // Actualizar el flujo de trabajo y notificar al usuario
            $this->workflowService->completeStep($processLog, $step);
            return [
                'success' => true,
                'message' => 'Importación completada con éxito',
                'data' => [
                    'file' => $uploadedFile->id,
                    'tableName' => $tableName,
                    'step' => $step,
                    'recordsProcessed' => $mappedData->count()
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al insertar los datos en la base de datos',
                'data' => ['file' => $uploadedFile->id, 'tableName' => $tableName, 'step' => $step]
            ];
        }

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
