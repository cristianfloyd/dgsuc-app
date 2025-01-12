<?php

namespace App\Imports;

use App\DTOs\ImportResultDTO;
use App\Enums\BloqueosEstadoEnum;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Support\Collection;
use App\Data\Reportes\BloqueosData;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use App\Exceptions\ValidationException;
use App\Models\Reportes\BloqueosDataModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Services\Imports\ImportNotificationService;
use App\Services\Imports\DuplicateValidationService;
use App\Services\Validation\ExcelRowValidationService;
use App\Services\Reportes\Interfaces\BloqueosServiceInterface;

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
        private readonly ExcelRowValidationService $rowValidator
    ) {
        $this->connection = $this->getConnectionFromTrait();
        $this->processedRows = collect();
        $this->importResult = new ImportResultDTO(
            message: 'Iniciando importación de bloqueos'
        );

        Log::debug('BloqueosImport constructor called', [
            'nroLiqui' => $this->nroLiqui,
        ]);
    }





    public function collection(Collection $rows): void
    {
        try {
            $this->connection->transaction(function () use ($rows) {
                $rows->chunk($this->chunkSize())->each(function ($chunk) {
                    $this->processChunk($chunk);
                });
            });

            $this->importResult->success = true;
            $this->importResult->message = 'Importación completada exitosamente';
        } catch (\Exception $e) {
            $this->importResult->success = false;
            $this->importResult->error = $e;
            $this->importResult->message = 'Error en la importación: ' . $e->getMessage();
            $this->importResult->addError($e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    private function processChunk(Collection $chunk): void
    {
        $this->connection->transaction(function () use ($chunk) {
            // Validación de duplicados
            $validatedChunk = $this->validateChunk($chunk);

            // Procesamiento de registros válidos
            $validatedChunk->each(function ($row) {
                $this->processRow($row->toArray());
            });
        });
    }

    private function validateChunk(Collection $chunk): Collection
    {
        $this->duplicateValidator->processRecords($chunk);
        return $this->duplicateValidator->getValidRecords();
    }

    private function processRow(array $row): void
    {
        try {
            // Validación completa de la fila
            $validatedData = $this->rowValidator->validateRow($row);

            // Si hay error de validación, guardamos el registro con el error
            if ($validatedData['estado'] === BloqueosEstadoEnum::ERROR_VALIDACION) {
                BloqueosDataModel::create([
                    'email' => $validatedData['correo_electronico'],
                    'nombre' => $validatedData['nombre'],
                    'usuario_mapuche' => $validatedData['usuario_mapuche_solicitante'],
                    'dependencia' => $validatedData['dependencia'],
                    'nro_legaj' => $validatedData['legajo'],
                    'nro_cargo' => $validatedData['n_de_cargo'],
                    'fecha_baja' => $validatedData['fecha_de_baja'] ?? null,
                    'tipo' => $validatedData['tipo_de_movimiento'],
                    'observaciones' => $validatedData['observaciones'] ?? '',
                    'nro_liqui' => $this->nroLiqui,
                    'estado' => $validatedData['estado'],
                    'mensaje_error' => $validatedData['mensaje_error']
                ]);

                $this->importResult->incrementErrorCount();
                return;
            }

            // Transformación a DTO
            $bloqueosData = BloqueosData::fromValidatedData($validatedData, $this->nroLiqui);

            // Creación del registro en BD con estado inicial
            $bloqueosModel = BloqueosDataModel::create([
                $bloqueosData->toArray(),
                'estado' => BloqueosEstadoEnum::IMPORTADO
            ]);

            // Procesamiento
            $result = $this->bloqueosService->processImport($bloqueosData->toArray());

            // Actualización exitosa del registro
            $bloqueosModel->update([
                'estado' => BloqueosEstadoEnum::VALIDADO
            ]);

            $this->processedRows->push($result);
            $this->importResult->incrementProcessedCount();
        } catch (ValidationException $e) {
            // Guardamos el registro con el error de validación
            BloqueosDataModel::create([
                'email' => $row['correo_electronico'],
                'nombre' => $row['nombre'],
                'usuario_mapuche' => $row['usuario_mapuche_solicitante'],
                'dependencia' => $row['dependencia'],
                'nro_legaj' => $row['legajo'],
                'nro_cargo' => $row['n_de_cargo'],
                'fecha_baja' => $row['fecha_de_baja'] ?? null,
                'tipo' => $row['tipo_de_movimiento'],
                'observaciones' => $row['observaciones'] ?? '',
                'nro_liqui' => $this->nroLiqui,
                'estado' => BloqueosEstadoEnum::ERROR_VALIDACION,
                'mensaje_error' => $e->getMessage()
            ]);

            $this->importResult->incrementErrorCount();
            Log::warning('Error de validación en fila', [
                'row' => $row,
                'error' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            // Guardamos el registro con error general
            BloqueosDataModel::create([
                'email' => $row['correo_electronico'],
                'nombre' => $row['nombre'],
                'usuario_mapuche' => $row['usuario_mapuche_solicitante'],
                'dependencia' => $row['dependencia'],
                'nro_legaj' => $row['legajo'],
                'nro_cargo' => $row['n_de_cargo'],
                'fecha_baja' => $row['fecha_de_baja'] ?? null,
                'tipo' => $row['tipo_de_movimiento'],
                'observaciones' => $row['observaciones'] ?? '',
                'nro_liqui' => $this->nroLiqui,
                'estado' => BloqueosEstadoEnum::ERROR_PROCESO,
                'mensaje_error' => $e->getMessage()
            ]);

            $this->importResult->incrementErrorCount();
            Log::error('Error procesando fila', [
                'row' => $row,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function logImportStatistics(): void
    {
        Log::info('Importación completada', [
            'processed' => $this->importResult->getProcessedCount(),
            'duplicates' => $this->importResult->getDuplicateCount(),
            'errors' => $this->importResult->getErrorCount()
        ]);
    }





    /**
     * Define la fila que contiene los encabezados
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
     * Mensajes personalizados para las validaciones
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
}
