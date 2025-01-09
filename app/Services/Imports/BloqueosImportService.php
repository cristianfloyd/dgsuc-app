<?php

namespace App\Services\Imports;

use PDO;
use App\Imports\BloqueosImport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\MapucheConnectionTrait;
use App\Exceptions\DuplicateCargoException;
use App\Exceptions\Imports\ImportException;

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
        $startTime = now();
        $context = [
            'file' => $filePath,
            'liquidacion' => $nroLiqui,
            'start_time' => $startTime->toDateTimeString(),
        ];

        try {
            // Validar archivo antes de procesar
            $this->validationService->validateFile($filePath);

            $connection->beginTransaction();

            // Log del estado de la conexión
            Log::info('Estado de conexión', [
                ...$context,
                'connection_status' => $connection->getPdo()->getAttribute(PDO::ATTR_CONNECTION_STATUS),
                'transaction_active' => $connection->transactionLevel() > 0
            ]);

            $importer = new BloqueosImport(
                $nroLiqui,
                app(DuplicateValidationService::class)
            );

            Excel::import($importer, $filePath);

            $processedCount = $importer->getProcessedRowsCount();

            $connection->commit();

            // Log de finalización exitosa
            Log::info('Importación completada exitosamente', [
                ...$context,
                'processed_rows' => $processedCount,
                'duration_seconds' => now()->diffInSeconds($startTime),
                'memory_usage' => memory_get_peak_usage(true) / 1024 / 1024 . 'MB'
            ]);
            $this->notificationService->sendSuccessNotification();

        } catch (DuplicateCargoException $e){

            $connection->rollBack();
            Log::warning('Duplicados encontrados en importación', [
                ...$context,
                'error_type' => 'duplicate_cargo',
                'error_message' => $e->getMessage(),
                'duplicate_values' => $e->getDuplicates() ?? [],
                'duration_seconds' => now()->diffInSeconds($startTime)
            ]);

            $this->notificationService->sendWarningNotification(
                'Duplicados encontrados',
                $e->getMessage()
            );

            throw $e;

        } catch (\Exception $e) {
            $connection->rollBack();

            // Log detallado del error
            Log::error('Error en importación', [
                ...$context,
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'duration_seconds' => now()->diffInSeconds($startTime),
                'memory_usage' => memory_get_peak_usage(true) / 1024 / 1024 . 'MB',
                'trace' => $e->getTraceAsString()
            ]);

            $this->notificationService->sendErrorNotification($e->getMessage());

            throw new ImportException(
                'Error al procesar la importación: ' . $e->getMessage(),
                previous: $e
            );
        }
    }
}
