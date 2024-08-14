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
    ){
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
    public function handle(): void
    {
        if (!$this->uploadedFileId) {
            throw new \Exception('No se proporcionó un archivo cargado.');
        }
        $uploadedFile = UploadedFile::findOrFail($this->uploadedFileId);
        Log::info('Iniciando ImportAfipRelacionesActivasJob');

        try {
            $processLog = $this->workflowService->getLatestWorkflow();
            $currentStep = $this->workflowService->getCurrentStep($processLog);
            $this->workflowService->updateStep($processLog, $currentStep, 'in_progress');

            $this->transactionService->executeInTransaction(function () use ($uploadedFile) {
                $this->validationService->validateSelectedFile($uploadedFile);
                $filePath = $uploadedFile->file_path;
                log::info("Desde el Job, invocamos a processFile(): $filePath");
                $processedLines = $this->fileProcessor->processFile(
                    $uploadedFile->file_path,
                    $this->columnMetadata->getWidths(),
                    $uploadedFile,
                );

                $this->employeeService->storeProcessedLines($processedLines->toArray());
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
