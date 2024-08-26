<?php

namespace App\Jobs;

use App\Events\JobFailed;
use App\Events\JobProcessed;
use App\Models\UploadedFile;
use App\Services\ColumnMetadata;
use App\Services\EmployeeService;
use App\Services\ValidationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Contracts\FileProcessorInterface;
use Illuminate\Foundation\Queue\Queueable;
use App\Contracts\WorkflowServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Contracts\TransactionServiceInterface;

class ImportAfipRelacionesActivasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $fileProcessor;
    private $employeeService;
    private $validationService;
    private $transactionService;
    private $workflowService;
    private $columnMetadata;

    public function __construct(
        FileProcessorInterface $fileProcessor,
        EmployeeService $employeeService,
        ValidationService $validationService,
        TransactionServiceInterface $transactionService,
        WorkflowServiceInterface $workflowService,
        ColumnMetadata $columnMetadata,
        protected $uploadedFileId = null,
    ) {
        $this->fileProcessor = $fileProcessor;
        $this->employeeService = $employeeService;
        $this->validationService = $validationService;
        $this->transactionService = $transactionService;
        $this->workflowService = $workflowService;
        $this->columnMetadata = $columnMetadata;
        $this->uploadedFileId = $uploadedFileId;
    }

    /**
     * Execute the job.
     */
    public function handle(): array
    {
        $uploadedFile = UploadedFile::findOrFail($this->uploadedFileId);

        return $this->transactionService->executeInTransaction(
            function () use ($uploadedFile): array {
                try {
                    // Validar el archivo seleccionado
                    $this->validationService->validateSelectedFile($uploadedFile);

                    // Obtener la ruta del archivo
                    $filePath = $uploadedFile->file_path;
                    Log::info("Iniciando procesamiento del archivo: $filePath");

                    // Procesar el archivo
                    $processedLines = $this->fileProcessor->processFile(
                        $uploadedFile->file_path,
                        $this->columnMetadata->getWidths(),
                        $uploadedFile
                    );

                    $storeResult = $this->employeeService->storeProcessedLines($processedLines->toArray());

                    if ($storeResult) {
                        return [
                            'success' => true,
                            'message' => "Importación exitosa",
                            'data' => ['linesProcessed' => count($processedLines)]
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
