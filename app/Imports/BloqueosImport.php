<?php

namespace App\Imports;

use App\Data\Reportes\BloqueosData;
use App\DTOs\ImportResultDTO;
use App\Enums\BloqueosEstadoEnum;
use App\Models\Reportes\BloqueosDataModel;
use App\Services\Imports\DuplicateValidationService;
use App\Services\Imports\ImportNotificationService;
use App\Services\Reportes\Interfaces\BloqueosServiceInterface;
use App\Services\Validation\ExcelRowValidationService;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class BloqueosImport implements ToCollection, WithHeadingRow, WithValidation, WithChunkReading
{
    use MapucheConnectionTrait;
    use Importable;

    public $connection;

    private Collection $processedRows;

    private ImportResultDTO $importResult;

    /**
     * Constructor de la clase BloqueosImport.
     *
     * Inicializa la conexión a la base de datos y el servicio de validación de duplicados.
     *
     * @param int $nroLiqui Número de liquidación.
     * @param DuplicateValidationService $duplicateValidator Servicio de validación de duplicados.
     */
    public function __construct(
        private readonly int $nroLiqui,
        private readonly DuplicateValidationService $duplicateValidator,
        private readonly BloqueosServiceInterface $bloqueosService,
        private readonly ImportNotificationService $notificationService,
        private readonly ExcelRowValidationService $rowValidator,
    ) {
        $this->connection = $this->getConnectionFromTrait();
        $this->processedRows = collect();
        $this->importResult = new ImportResultDTO(
            message: 'Iniciando importación de bloqueos',
        );

        Log::debug('BloqueosImport constructor called', [
            'nroLiqui' => $this->nroLiqui,
        ]);
    }

    public function collection(Collection $rows): void
    {
        try {
            // Procesamiento por lotes dentro de una transacción
            $this->connection->transaction(function () use ($rows): void {
                $rows->chunk($this->chunkSize())->each(function ($chunk): void {
                    $this->processChunk($chunk);
                });
            });

            // Actualización del resultado final
            $this->importResult->success = true;
            $this->importResult->message = 'Importación completada exitosamente';
        } catch (\Exception $e) {
            $this->importResult->success = false;
            $this->importResult->error = $e;
            $this->importResult->message = 'Error en la importación: ' . $e->getMessage();
            $this->importResult->addError($e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Define la fila que contiene los encabezados.
     *
     * @return int
     */
    public function headingRow(): int
    {
        return 1;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function rules(): array
    {
        return BloqueosData::rules();
    }

    public function getProcessedRowsCount(): int
    {
        return $this->processedRows->count();
    }

    public function getImportResult(): ImportResultDTO
    {
        return $this->importResult;
    }

    /**
     * Mensajes personalizados para las validaciones.
     *
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            'correo_electronico.required' => 'El campo email es obligatorio',
            'correo_electronico.email' => 'El email debe tener un formato válido',
            'nombre.required' => 'El campo nombre es obligatorio',
            'usuario_mapuche_solicitante.required' => 'El usuario Mapuche es obligatorio',
            'dependencia.required' => 'La dependencia es obligatoria',
            'legajo.required' => 'El número de legajo es obligatorio',
            'legajo.numeric' => 'El número de legajo debe ser numérico',
            'n_de_cargo.required' => 'El número de cargo es obligatorio',
            'n_de_cargo.numeric' => 'El número de cargo debe ser numérico',
            'tipo_de_movimiento.required' => 'El tipo es obligatorio',
            'tipo_de_movimiento.in' => 'El tipo debe ser: Licencia, Fallecido o Renuncia',
        ];
    }

    private function processChunk(Collection $chunk): void
    {
        // Validación de duplicados
        $validatedChunk = $this->validateChunk($chunk);

        // Procesamiento de registros válidos
        $validatedChunk->each(function ($row): void {
            $this->processRow($row->toArray());
        });
    }

    private function validateChunk(Collection $chunk): Collection
    {
        $this->duplicateValidator->processRecords($chunk);
        return $this->duplicateValidator->getValidRecords();
    }

    private function processRow(array $row): void
    {
        // Validación completa de la fila
        $validatedData = $this->rowValidator->validateRow($row);


        // Transformación a DTO
        $bloqueosData = BloqueosData::fromValidatedData($validatedData, $this->nroLiqui);

        // Crea un nuevo registro en la base de datos utilizando el modelo BloqueosDataModel con los datos validados convertidos a un array
        $bloqueosModel = BloqueosDataModel::create($bloqueosData->toArray());

        // 4. Actualización de contadores
        if ($bloqueosData->estado === BloqueosEstadoEnum::VALIDADO) {
            // Agrega el modelo de bloqueos procesado a la colección de filas procesadas
            $this->processedRows->push($bloqueosModel);
            $this->importResult->incrementProcessedCount();
        } else {
            $this->importResult->incrementErrorCount();
        }
    }

    private function logImportStatistics(): void
    {
        Log::info('Importación completada', [
            'processed' => $this->importResult->getProcessedCount(),
            'duplicates' => $this->importResult->getDuplicateCount(),
            'errors' => $this->importResult->getErrorCount(),
        ]);
    }
}
