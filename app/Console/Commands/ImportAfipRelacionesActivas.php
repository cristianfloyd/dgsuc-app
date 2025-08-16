<?php

namespace App\Console\Commands;

use app\Contracts\EmployeeServiceInterface;
use app\Contracts\FileProcessorInterface;
use app\Contracts\TransactionServiceInterface;
use app\Contracts\WorkflowServiceInterface;
use app\Models\UploadedFile;
use app\Services\ColumnMetadata;
use app\Services\ValidationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportAfipRelacionesActivas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afip:import {uploadedFileId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa las relaciones activas desde un archivo AFIP.';

    protected $fileProcessor;

    protected $employeeService;

    protected $validationService;

    protected $transactionService;

    protected $workflowService;

    protected $columnMetadata;

    public function __construct(
        FileProcessorInterface $fileProcessor,
        EmployeeServiceInterface $employeeService,
        ValidationService $validationService,
        TransactionServiceInterface $transactionService,
        WorkflowServiceInterface $workflowService,
        ColumnMetadata $columnMetadata,
    ) {
        parent::__construct();
        // ... (Asignar las dependencias a las propiedades)
        $this->fileProcessor = $fileProcessor;
        $this->employeeService = $employeeService;
        $this->validationService = $validationService;
        $this->transactionService = $transactionService;
        $this->workflowService = $workflowService;
        $this->columnMetadata = $columnMetadata;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $uploadedFileId = $this->argument('uploadedFileId'); // Obtener el ID del archivo
        Log::info('Importando archivo con ID: ' . $uploadedFileId);
        try {
            // 1. Obtener el archivo a procesar desde la base de datos
            $uploadedFile = UploadedFile::query()->findOrFail($uploadedFileId)->first();

            // 2. Actualizar el step en el flujo de trabajo
            $processLog = $this->workflowService->getLatestWorkflow();
            $currentStep = $this->workflowService->getCurrentStep($processLog);
            $this->workflowService->updateStep($processLog, $currentStep, 'in_progress');

            $this->transactionService->executeInTransaction(function () use ($uploadedFile): void {
                // 3. Validar el archivo
                $this->validationService->validateSelectedFile($uploadedFile);

                // 4. Procesar el archivo
                $processedLines = $this->fileProcessor->processFile(
                    $uploadedFile,
                    $this->columnMetadata->getWidths(),
                );

                // 5. Guardar las líneas procesadas
                Log::info('Guardando líneas procesadas (' . $processedLines->count() . ') AfipRelacionesActivas');
                $this->employeeService->storeProcessedLines($processedLines->toArray());
            });

            // 6. Marcar el step como completado
            $this->workflowService->completeStep($processLog, 'import_archivo_afip');

            $this->info('Archivo importado correctamente.');
        } catch (\Exception $e) {
            // 7. Manejar errores, marcar el proceso como fallido
            $this->workflowService->failStep('import_archivo_afip', $e->getMessage());

            $this->error('Error al importar el archivo: ' . $e->getMessage());
        }
    }
}
