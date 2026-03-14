<?php

namespace App\Services\Imports;

use App\Exceptions\Imports\ImportException;
use App\Exceptions\ImportValidationException;
use App\Traits\MapucheConnectionTrait;
use Exception;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

use function is_callable;

class BloqueosImportService
{
    use MapucheConnectionTrait;

    public function __construct(
        private readonly ImportValidationService $validationService,
        private readonly ImportNotificationService $notificationService,
    ) {}

    public function processImport(array $row, int $nroLiqui): array
    {
        $this->validationService->validateFile('');
        $startTime = now();

        try {
            // Procesar la fila individual
            $processedRow = $this->processRow($row);

            Log::info('Fila procesada exitosamente', [
                'nro_liqui' => $nroLiqui,
                'processed_data' => $processedRow,
                'duration_ms' => now()->diffInMilliseconds($startTime),
            ]);

            return $processedRow;
        } catch (Exception $e) {
            Log::error('Error procesando fila', [
                'row' => $row,
                'error' => $e->getMessage(),
            ]);
            $this->notificationService->sendErrorNotification($e->getMessage());

            throw new ImportException('Error procesando fila: ' . $e->getMessage(), $e->getCode(), previous: $e);
        }
    }

    /**
     * Procesa y valida los datos del archivo Excel.
     *
     * @param array $row Fila del Excel
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
     * Parsea y valida fechas en múltiples formatos.
     *
     * @param mixed $date
     */
    private function parseDate($date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            // Intentar diferentes formatos de fecha
            $formats = [
                'd/m/Y',
                'Y-m-d',
                'd-m-Y',
                'd/m/y',
                'Y/m/d',
                // Formato Excel (número de días desde 1900)
                fn($value) => \Illuminate\Support\Facades\Date::instance(Date::excelToDateTimeObject($value)),
            ];

            foreach ($formats as $format) {
                try {
                    $parsed = is_callable($format) ? $format($date) : \Illuminate\Support\Facades\Date::createFromFormat($format, $date);

                    if ($parsed && $parsed->year > 1900 && $parsed->year < 2100) {
                        return $parsed->format('Y-m-d');
                    }
                } catch (Exception) {
                    continue;
                }
            }

            throw new Exception('Formato de fecha no válido');
        } catch (Exception) {
            throw new ImportValidationException("Error al procesar fecha: {$date}");
        }
    }

    /**
     * Valida el formato del legajo.
     *
     * @param mixed $legajo
     */
    private function validateLegajo($legajo): int
    {
        if (!is_numeric($legajo) || $legajo < 1) {
            throw new ImportValidationException("Legajo inválido: {$legajo}");
        }

        return (int) $legajo;
    }

    /**
     * Valida el formato del cargo.
     *
     * @param mixed $cargo
     */
    private function validateCargo($cargo): int
    {
        if (!is_numeric($cargo) || $cargo < 1) {
            throw new ImportValidationException("Cargo inválido: {$cargo}");
        }

        return (int) $cargo;
    }
}
