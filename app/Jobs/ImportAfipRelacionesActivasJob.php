<?php

namespace App\Jobs;

use App\Models\UploadedFile;
use App\Services\ColumnMetadata;
use App\Services\EmployeeService;
use App\Services\ValidationService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Contracts\FileProcessorInterface;
use Illuminate\Foundation\Queue\Queueable;
use App\Contracts\WorkflowServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Contracts\TransactionServiceInterface;
use Illuminate\Support\Facades\Log;

class ImportAfipRelacionesActivasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $uploadedFileId;

    public function __construct($uploadedFileId)
    {
        $this->uploadedFileId = $uploadedFileId;
    }

    /**
     * Execute the job.
     */
    public function handle(
        FileProcessorInterface $fileProcessor,
        EmployeeService $employeeService,
        ValidationService $validationService,
        TransactionServiceInterface $transactionService,
        WorkflowServiceInterface $workflowService,
        ColumnMetadata $columnMetadata
    ) {
        $uploadedFile = UploadedFile::findOrFail($this->uploadedFileId);
        Log::info('Iniciando ImportAfipRelacionesActivasJob');
        try {
            $processLog = $workflowService->getLatestWorkflow();
            $currentStep = $workflowService->getCurrentStep($processLog);

            $workflowService->updateStep($processLog, $currentStep, 'in_progress');

            $transactionService->executeInTransaction(function () use ($uploadedFile, $fileProcessor, $employeeService, $validationService, $columnMetadata) {
                $validationService->validateSelectedFile($uploadedFile);

                $lineasProcesadas = $fileProcessor->processFile(
                    $uploadedFile,
                    $columnMetadata->getWidths()
                );

                $employeeService->storeProcessedLines($lineasProcesadas);
            });

            $workflowService->completeStep($processLog, 'import_archivo_afip');

            // Log::info('Archivo importado correctamente.');
        } catch (\Exception $e) {
            $workflowService->failStep('import_archivo_afip', $e->getMessage());

            // Log::error('Error al importar el archivo: ' . $e->getMessage());
            // Puedes manejar el error aquí o lanzarlo de nuevo para que lo maneje un listener
            throw $e;
        }
    }
}
