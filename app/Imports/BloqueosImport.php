<?php

namespace App\Imports;

use App\Data\Reportes\BloqueosData;
use App\Models\Reportes\BloqueosDataModel;
use App\Services\Imports\DuplicateValidationService;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

    private string $connection;
    private Collection $rows;
    private Collection $processedRows;
    private DuplicateValidationService $duplicateValidator;

    /**
     * Constructor de la clase BloqueosImport.
     *
     * Inicializa la conexión a la base de datos y el servicio de validación de duplicados.
     *
     * @param int $nroLiqui Número de liquidación.
     * @param DuplicateValidationService $duplicateValidator Servicio de validación de duplicados.
     */
    public function __construct(private readonly int $nroLiqui, DuplicateValidationService $duplicateValidator)
    {
        $this->connection = $this->getConnectionName();
        $this->duplicateValidator = $duplicateValidator;
    }


    public function collection(Collection $rows)
    {
        try {
            DB::beginTransaction();

            // Validación de duplicados en el Excel
            $this->duplicateValidator->validateExcelDuplicates($rows);

            // Transformación y validación mediante DTOs
            $dtos = $rows->map(fn($row) =>
                BloqueosData::fromExcelRow($row, $this->nroLiqui)
            );

            // Procesamiento en lotes para mejor rendimiento
            $dtos->chunk(100)->each(function($chunk) {
                $records = $chunk->map(function($dto) {
                    return $dto->toArray();
                });

                BloqueosDataModel::insert($records->all());
                $this->processedRows = $this->processedRows->merge($chunk);
            });



            DB::commit();

            Log::info('Importación completada', [
                'total_registros' => $this->processedRows->count()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en importación', [
                'message' => $e->getMessage(),
                'processed_rows' => $this->processedRows->count()
            ]);
            throw $e;
        }
    }



    // public function model(array $row)
    // {
    //     $fechaBaja = null;
    //     $chkstopliq = false;

    //     $tipoMovimiento = strtolower($row['tipo_de_movimiento']);

    //     if ($tipoMovimiento === 'licencia') {
    //         $chkstopliq = true;
    //     } elseif (in_array($tipoMovimiento, ['fallecido', 'renuncia']) && !empty($row['fecha_de_baja'])) {
    //         $fechaBaja = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha_de_baja']))
    //             ->format('Y-m-d');
    //         if (Carbon::parse($fechaBaja)->day === 1) {
    //             $fechaBaja = Carbon::parse($fechaBaja)
    //                 ->subMonth()
    //                 ->endOfMonth()
    //                 ->format('Y-m-d');
    //         }
    //     }



    //     // Convertir hora de finalización de Excel a Carbon
    //     $fechaRegistro = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['hora_de_finalizacion']));


    //     return new BloqueosDataModel([
    //         'fecha_registro' => $fechaRegistro,
    //         'email' => $row['correo_electronico'],
    //         'nombre' => $row['nombre'],
    //         'usuario_mapuche' => $row['usuario_mapuche_solicitante'],
    //         'dependencia' => $row['dependencia'],
    //         'nro_legaj' => $row['legajo'],
    //         'nro_cargo' => $row['n_de_cargo'],
    //         'fecha_baja' => $fechaBaja,
    //         'tipo' => $row['tipo_de_movimiento'],
    //         'observaciones' => $row['observaciones'] ?? null,
    //         'chkstopliq' => $chkstopliq,
    //         'nro_liqui' => $this->nroLiqui,
    //     ]);
    // }

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

    /**
     * Reglas de validación para los datos importados
     * @return array
     */
    // public function rules(): array
    // {
    //     return [
    //         'correo_electronico' => ['required', 'email'],
    //         'nombre' => ['required', 'string'],
    //         'usuario_mapuche_solicitante' => ['required', 'string'],
    //         'dependencia' => ['required', 'string'],
    //         'legajo' => ['required', 'numeric'],
    //         'n_de_cargo' => ['required', 'numeric'],
    //         'tipo_de_movimiento' => ['required', 'string', 'in:Licencia,Fallecido,Renuncia'],
    //     ];
    // }

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
