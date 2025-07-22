<?php

namespace App\Services;

use App\Contracts\DatabaseServiceInterface;
use App\Contracts\FileProcessorInterface;
use App\Contracts\TableManagementServiceInterface;
use App\Contracts\WorkflowServiceInterface;
use App\Models\UploadedFile;
use Illuminate\Support\Facades\Log;

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
        DatabaseServiceInterface $databaseService,
    ) {
        $this->fileProcessor = $fileProcessor;
        $this->workflowService = $workflowService;
        $this->tableManagementService = $tableManagementService;
        $this->databaseService = $databaseService;
    }

    /**
     * Importa un archivo y procesa sus datos.
     *
     * @param UploadedFile $file El archivo a importar.
     * @param string $tableName El nombre de la tabla donde se insertarán los datos.
     * @param string $step El paso del flujo de trabajo actual.
     *
     * @return array Un array con información sobre el resultado de la importación.
     */
    public function importarArchivo(UploadedFile $file, string $tableName, string $step): array
    {
        try {
            $processLog = $this->workflowService->getLatestWorkflow();
            $system = $file->origen;

            // Paso 1: Procesar el archivo y obtener los datos mapeados
            $mappedData = $this->fileProcessor->handleFileImport($file, $system);
            Log::info('Datos mapeados:', [$mappedData->count()]);

            if ($mappedData->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No se encontraron datos para procesar',
                    'data' => ['file' => $file->id, 'tableName' => $tableName, 'step' => $step],
                ];
            }

            // Paso 2: Verificar y preparar la tabla
            Log::info('Verificando y preparando tabla:', [$tableName]);
            $tableResult = $this->tableManagementService->verifyAndPrepareTable($tableName);

            if (!$tableResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Error al verificar y preparar la tabla: ' . $tableResult['message'],
                    'data' => array_merge(['file' => $file->id, 'step' => $step], $tableResult['data']),
                    'error' => $tableResult['error'] ?? null,
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
                        'file' => $file->id,
                        'tableName' => $tableName,
                        'step' => $step,
                        'recordsProcessed' => $mappedData->count(),
                    ],
                ];
            }
            return [
                'success' => false,
                'message' => 'Error al insertar los datos en la base de datos',
                'data' => ['file' => $file->id, 'tableName' => $tableName, 'step' => $step],
            ];
        } catch (\Exception $e) {
            Log::error('Error durante la importación: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error durante la importación: ' . $e->getMessage(),
                'data' => [
                    'file' => $file->id,
                    'tableName' => $tableName,
                    'step' => $step,
                    'error' => $e->getMessage(),
                ],
            ];
        }
    }
}
