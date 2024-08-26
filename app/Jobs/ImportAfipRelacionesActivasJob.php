<?php

namespace App\Jobs;

use App\Models\UploadedFile;
use App\Services\ColumnMetadata;
use App\Services\EmployeeService;
use App\Services\ValidationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Contracts\FileProcessorInterface;
use Illuminate\Foundation\Queue\Queueable;
use App\Contracts\DatabaseServiceInterface;
use App\Contracts\WorkflowServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Contracts\TransactionServiceInterface;
use App\Contracts\TableManagementServiceInterface;

class ImportAfipRelacionesActivasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private static $tableName = 'afip_relaciones_activas';
    private $fileProcessor;
    private $employeeService;
    private $validationService;
    private $transactionService;
    private $workflowService;
    private $columnMetadata;
    private $databaseService;
    private $tableManagementService;

    public function __construct(
        FileProcessorInterface $fileProcessor,
        EmployeeService $employeeService,
        ValidationService $validationService,
        TransactionServiceInterface $transactionService,
        WorkflowServiceInterface $workflowService,
        ColumnMetadata $columnMetadata,
        DatabaseServiceInterface $databaseService,
        TableManagementServiceInterface $tableManagementService,
        protected $uploadedFileId = null,
    ) {
        $this->fileProcessor = $fileProcessor;
        $this->employeeService = $employeeService;
        $this->validationService = $validationService;
        $this->transactionService = $transactionService;
        $this->workflowService = $workflowService;
        $this->columnMetadata = $columnMetadata;
        $this->databaseService = $databaseService;
        $this->tableManagementService = $tableManagementService;
        $this->uploadedFileId = $uploadedFileId;
    }

    /**
     * Execute the job.
     */
    public function handle(): array
    {
        $uploadedFile = UploadedFile::findOrFail($this->uploadedFileId);
        $system = $uploadedFile->origen;
        Log::info('sistema: ' . $system);
        $tableName = self::$tableName;
        Log::info('tabla: ' . $tableName);

        $processLog = $this->workflowService->getLatestWorkflow();
        $step = $this->workflowService->getCurrentStep($processLog);

        return $this->transactionService->executeInTransaction(

            function () use ($uploadedFile, $system, $tableName, $step): array {
                try {
                    // Verificar que el sistema sea afip

                    // Validar el archivo seleccionado
                    $this->validationService->validateSelectedFile($uploadedFile);

                    // Obtener la ruta del archivo
                    $filePath = $uploadedFile->file_path;
                    Log::info("Iniciando procesamiento del archivo: $filePath");

                    // Procesar el archivo
                    // $processedLines = $this->fileProcessor->processFile(
                    //     $uploadedFile->file_path,
                    //     $this->columnMetadata->getWidths(),
                    //     $uploadedFile
                    // );
                    $mappedData = $this->fileProcessor->handleFileImport($uploadedFile, $system);
                    Log::info('Datos mapeados:', [$mappedData->count()]);

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

                    // Almacenar los datos procesados en la base de datos
                    $insertResult = $this->databaseService->insertBulkData($mappedData, $tableName);



                    if ($insertResult['success']) {
                        return [
                            'success' => true,
                            'message' => "Importación exitosa",
                            'data' => ['linesProcessed' => $mappedData->count()]
                        ];
                    } else {
                        return [
                            'success' => false,
                            'message' => "Error al almacenar las líneas procesadas",
                            'data' => []
                        ];
                    }

                } catch (\Exception $e) {
                    Log::error("Error durante la importación: " . $e->getMessage());
                    return [
                        'success' => false,
                        'message' => "Error durante la importación: " . $e->getMessage(),
                        'data' => [],
                        'error' => $e->getMessage()
                    ];
                }
            }
        );
    }
}
