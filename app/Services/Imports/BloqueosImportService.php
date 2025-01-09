<?php

namespace App\Services\Imports;

use App\Imports\BloqueosImport;
use App\Exceptions\ImportException;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\MapucheConnectionTrait;

class BloqueosImportService
{
    use MapucheConnectionTrait;

    private ImportValidationService $validationService;
    private ImportNotificationService $notificationService;

    public function __construct(
        ImportValidationService $validationService,
        ImportNotificationService $notificationService
    ) {
        $this->validationService = $validationService;
        $this->notificationService = $notificationService;
    }

    public function processImport(string $filePath, int $nroLiqui): void
    {
        $connection = $this->getConnectionFromTrait();

        try {
            // Validar archivo antes de procesar
            $this->validationService->validateFile($filePath);

            $connection->beginTransaction();

            Log::info('Iniciando importación', [
                'file' => $filePath,
                'liquidacion' => $nroLiqui
            ]);

            $importer = new BloqueosImport($nroLiqui);
            Excel::import($importer, $filePath);

            $connection->commit();

            $this->notificationService->sendSuccessNotification();

            Log::info('Importación completada exitosamente');

        } catch (\Exception $e) {
            $connection->rollBack();

            Log::error('Error en importación', [
                'message' => $e->getMessage(),
                'file' => $filePath
            ]);

            $this->notificationService->sendErrorNotification($e->getMessage());

            throw new \Exception('Error al procesar la importación: ' . $e->getMessage());
        }
    }
}
