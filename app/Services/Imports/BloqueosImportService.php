<?php

namespace App\Services\Imports;

use PDO;
use Carbon\Carbon;
use App\Imports\BloqueosImport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\MapucheConnectionTrait;
use App\Services\Reportes\BloqueosService;
use App\Exceptions\DuplicateCargoException;
use App\Exceptions\Imports\ImportException;
use App\Exceptions\ImportValidationException;
use App\Services\Reportes\BloqueosProcessService;

class BloqueosImportService
{
    use MapucheConnectionTrait;

    private ImportValidationService $validationService;
    private ImportNotificationService $notificationService;
    private $connection;

    public function __construct(
        ImportValidationService $validationService,
        ImportNotificationService $notificationService
    ) {
        $this->validationService = $validationService;
        $this->notificationService = $notificationService;
        $this->connection = $this->getConnectionFromTrait();
    }

    public function processImport(array $row, int $nroLiqui): array
    {
        $startTime = now();


        try {
            // Procesar la fila individual
            $processedRow = $this->processRow($row);

            Log::info('Fila procesada exitosamente', [
                'nro_liqui' => $nroLiqui,
                'processed_data' => $processedRow,
                'duration_ms' => now()->diffInMilliseconds($startTime)
            ]);

            return $processedRow;


        } catch (\Exception $e) {
            Log::error('Error procesando fila', [
                'row' => $row,
                'error' => $e->getMessage()
            ]);

            throw new ImportException(
                'Error procesando fila: ' . $e->getMessage(),
                previous: $e
            );
        }
    }



    /**
     * Procesa y valida los datos del archivo Excel
     * @param array $row Fila del Excel
     * @return array
     */
    public function processRow(array $row): array
    {
        return [
            'fecha_baja' => $this->parseDate($row['fecha_de_baja']),
            'legajo' => $this->validateLegajo($row['legajo']),
            'cargo' => $this->validateCargo($row['cargo']),
        ];
    }

    /**
     * Parsea y valida fechas en múltiples formatos
     * @param mixed $date
     * @return string|null
     */
    private function parseDate($date): ?string
    {
        if (empty($date)) return null;

        try {
            // Intentar diferentes formatos de fecha
            $formats = [
                'd/m/Y', 'Y-m-d', 'd-m-Y',
                'd/m/y', 'Y/m/d',
                // Formato Excel (número de días desde 1900)
                function($value) {
                    return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
                }
            ];

            foreach ($formats as $format) {
                try {
                    if (is_callable($format)) {
                        $parsed = $format($date);
                    } else {
                        $parsed = Carbon::createFromFormat($format, $date);
                    }

                    if ($parsed && $parsed->year > 1900 && $parsed->year < 2100) {
                        return $parsed->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            throw new \Exception("Formato de fecha no válido");

        } catch (\Exception $e) {
            throw new ImportValidationException("Error al procesar fecha: {$date}");
        }
    }

    /**
     * Valida el formato del legajo
     * @param mixed $legajo
     * @return int
     */
    private function validateLegajo($legajo): int
    {
        if (!is_numeric($legajo) || $legajo < 1) {
            throw new ImportValidationException("Legajo inválido: {$legajo}");
        }
        return (int)$legajo;
    }

    /**
     * Valida el formato del cargo
     * @param mixed $cargo
     * @return int
     */
    private function validateCargo($cargo): int
    {
        if (!is_numeric($cargo) || $cargo < 1) {
            throw new ImportValidationException("Cargo inválido: {$cargo}");
        }
        return (int)$cargo;
    }

    private function buildLogContext(string $filePath, int $nroLiqui, Carbon $startTime): array
    {
        return [
            'file' => $filePath,
            'liquidacion' => $nroLiqui,
            'start_time' => $startTime->toDateTimeString(),
        ];
    }



    private function handleSuccessfulImport(BloqueosImport $importer, array $context): void
    {
        $processedCount = $importer->getProcessedRowsCount();

        Log::info('Importación completada exitosamente', [
            ...$context,
            'processed_rows' => $processedCount,
            'duration_seconds' => now()->diffInSeconds($context['start_time']),
            'memory_usage' => $this->getMemoryUsage()
        ]);

        $this->notificationService->sendSuccessNotification();
    }

    private function handleImportError(\Exception $e, array $context): void
    {
        Log::error('Error en importación', [
            ...$context,
            'error_type' => get_class($e),
            'error_message' => $e->getMessage(),
            'error_trace' => $e->getTraceAsString()
        ]);

        $this->notificationService->sendErrorNotification($e->getMessage());

        throw new ImportException(
            'Error al procesar la importación: ' . $e->getMessage(),
            previous: $e
        );
    }

    private function getMemoryUsage(): string
    {
        return memory_get_peak_usage(true) / 1024 / 1024 . 'MB';
    }
}
