<?php

namespace App\Services;

use App\Contracts\DatabaseServiceInterface;
use App\Models\UploadedFile;
use Illuminate\Support\Facades\Log;
use App\Contracts\FileProcessorInterface;
use App\Contracts\WorkflowServiceInterface;
use App\Contracts\TableManagementServiceInterface;

class SicossImportService
{
    private $fileProcessor;
    private $workflowService;
    private $tableManagementService;
    private $databaseService;

    public function __construct(
        FileProcessorInterface $fileProcessor,
        WorkflowServiceInterface $workflowService,
        TableManagementServiceInterface $tableManagementService,
        DatabaseServiceInterface $databaseService
    ) {
        $this->fileProcessor = $fileProcessor;
        $this->workflowService = $workflowService;
        $this->tableManagementService = $tableManagementService;
        $this->databaseService = $databaseService;
    }

    public function importarArchivo(UploadedFile $file, string $tableName, string $step): bool
    {
        $processLog = $this->workflowService->getLatestWorkflow();
        $system = $file->origen;

        // Paso 1: Procesar el archivo y obtener los datos mapeados
        $mappedData = $this->fileProcessor->handleFileImport($file, $system);
        Log::info('Datos mapeados:', [$mappedData->count()]);

        if ($mappedData->isNotEmpty()) {
            // Paso 2: Verificar y preparar la tabla
            Log::info('Verificando y preparando tabla:', [$tableName]);
            $this->tableManagementService->verifyAndPrepareTable($tableName);
            Log::info('Tabla verificada y preparada:', [$tableName]);

            // Paso 3: Insertar los datos mapeados en la base de datos
            $inserted = $this->databaseService->insertBulkData($mappedData, $tableName);

            if ($inserted) {
                // Actualizar el flujo de trabajo y notificar al usuario
                $this->workflowService->completeStep($processLog, $step);
                return true;
            }
        }

        return false;
    }
}
