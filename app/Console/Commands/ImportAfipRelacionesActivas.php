<?php

namespace App\Console\Commands;

use App\Models\UploadedFile;
use Illuminate\Console\Command;
use App\Services\ColumnMetadata;
use App\Services\ValidationService;
use Illuminate\Support\Facades\Log;
use App\Contracts\FileProcessorInterface;
use App\Contracts\EmployeeServiceInterface;
use App\Contracts\WorkflowServiceInterface;
use App\Contracts\TransactionServiceInterface;

class ImportAfipRelacionesActivas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature =  'afip:import {uploadedFileId}';

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
        ColumnMetadata $columnMetadata
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
    public function handle()
    {
        $uploadedFileId = $this->argument('uploadedFileId'); // Obtener el ID del archivo
        Log::info('Importando archivo con ID: ' . $uploadedFileId);
        try {
            // 1. Obtener el archivo a procesar desde la base de datos
            $uploadedFile = UploadedFile::findOrFail($uploadedFileId);

            // 2. Actualizar el step en el flujo de trabajo
            $processLog = $this->workflowService->getLatestWorkflow();
            $currentStep = $this->workflowService->getCurrentStep($processLog);
            $this->workflowService->updateStep($processLog, $currentStep, 'in_progress');

            $this->transactionService->executeInTransaction(function () use ($uploadedFile) {
                // 3. Validar el archivo
                $this->validationService->validateSelectedFile($uploadedFile);

                // 4. Procesar el archivo
                $lineasProcesadas = $this->fileProcessor->processFile(
                    $uploadedFile,
                    $this->columnMetadata->getWidths()
                );

                // 5. Guardar las líneas procesadas
                $this->employeeService->storeProcessedLines($lineasProcesadas);
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
