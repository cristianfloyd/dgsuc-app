<?php

namespace App\Services\Imports;

use App\Imports\BloqueosImport;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ImportException;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\MapucheConnectionTrait;
use App\Exceptions\DuplicateCargoException;

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

            DB::connection($this->getConnectionName())->beginTransaction();

            Log::info('Iniciando importación', [
                'file' => $filePath,
                'liquidacion' => $nroLiqui
            ]);

            $importer = new BloqueosImport(
                $nroLiqui,
                app(DuplicateValidationService::class)
            );

            Excel::import($importer, $filePath);

            DB::connection($this->getConnectionName())->commit();

            $this->notificationService->sendSuccessNotification();

            Log::info('Importación completada exitosamente');
        } catch (DuplicateCargoException $e){

            DB::connection($this->getConnectionName())->rollBack();
            $this->notificationService->sendWarningNotification(
                'Duplicados encontrados',
                $e->getMessage()
            );
            throw $e;

        } catch (\Exception $e) {
            DB::connection($this->getConnectionName())->rollBack();

            Log::error('Error en importación', [
                'message' => $e->getMessage(),
                'file' => $filePath
            ]);

            $this->notificationService->sendErrorNotification($e->getMessage());

            throw new \Exception('Error al procesar la importación: ' . $e->getMessage());
        }
    }
}
