<?php

namespace App\Jobs;

use App\Events\JobFailed;
use App\Events\JobProcessed;
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

class ImportAfipRelacionesActivasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function __construct(
        private FileProcessorInterface $fileProcessor,
        private EmployeeService $employeeService,
        private ValidationService $validationService,
        private TransactionServiceInterface $transactionService,
        private WorkflowServiceInterface $workflowService,
        private ColumnMetadata $columnMetadata,
        protected $uploadedFileId)
    {
        $this->uploadedFileId = $uploadedFileId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $uploadedFile = UploadedFile::findOrFail($this->uploadedFileId);
        Log::info('Iniciando ImportAfipRelacionesActivasJob');

        try {
            $processLog = $this->workflowService->getLatestWorkflow();
            $currentStep = $this->workflowService->getCurrentStep($processLog);
            $this->workflowService->updateStep($processLog, $currentStep, 'in_progress');

            $this->transactionService->executeInTransaction(function () use ($uploadedFile) {
                $this->validationService->validateSelectedFile($uploadedFile);

                $lineasProcesadas = $this->fileProcessor->processFile(
                    $uploadedFile,
                    $this->columnMetadata->getWidths()
                );

                $this->employeeService->storeProcessedLines($lineasProcesadas);
            });

            $this->workflowService->completeStep($processLog, 'import_archivo_afip');

            event(new JobProcessed($uploadedFile));
        } catch (\Exception $e) {
            $this->workflowService->failStep('import_archivo_afip', $e->getMessage());
            event(new JobFailed($e->getMessage()));

            // Puedes manejar el error aquí o lanzarlo de nuevo
            // throw $e;
        }
    }
}
