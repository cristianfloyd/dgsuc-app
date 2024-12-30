<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Maatwebsite\Excel\Concerns\ToModel;
use App\Models\Reportes\BloqueosDataModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class BloqueosImport implements ToModel, WithHeadingRow, WithValidation
{
    use MapucheConnectionTrait;
    use Importable;

    private $connection;
    private $nroLiqui;

    public function __construct($nroLiqui)
    {
        $this->connection = $this->getConnectionName();
        $this->nroLiqui = $nroLiqui;
    }

    public function model(array $row)
    {
        $fechaBaja = null;
        $chkstopliq = false;

        $tipoMovimiento = strtolower($row['tipo_de_movimiento']);

        if ($tipoMovimiento === 'licencia') {
            $chkstopliq = true;
        } elseif (in_array($tipoMovimiento, ['fallecido', 'renuncia']) && !empty($row['fecha_de_baja'])) {
            $fechaBaja = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha_de_baja']))
                ->format('Y-m-d');
            if (Carbon::parse($fechaBaja)->day === 1) {
                $fechaBaja = Carbon::parse($fechaBaja)
                    ->subMonth()
                    ->endOfMonth()
                    ->format('Y-m-d');
            }
        }



        // Convertir hora de finalización de Excel a Carbon
        $fechaRegistro = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['hora_de_finalizacion']));


        return new BloqueosDataModel([
            'fecha_registro' => $fechaRegistro,
            'email' => $row['correo_electronico'],
            'nombre' => $row['nombre'],
            'usuario_mapuche' => $row['usuario_mapuche_solicitante'],
            'dependencia' => $row['dependencia'],
            'nro_legaj' => $row['legajo'],
            'nro_cargo' => $row['n_de_cargo'],
            'fecha_baja' => $fechaBaja,
            'tipo' => $row['tipo_de_movimiento'],
            'observaciones' => $row['observaciones'] ?? null,
            'chkstopliq' => $chkstopliq,
            'nro_liqui' => $this->nroLiqui,
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

    /**
     * Reglas de validación para los datos importados
     * @return array
     */
    public function rules(): array
    {
        return [
            'correo_electronico' => ['required', 'email'],
            'nombre' => ['required', 'string'],
            'usuario_mapuche_solicitante' => ['required', 'string'],
            'dependencia' => ['required', 'string'],
            'legajo' => ['required', 'numeric'],
            'n_de_cargo' => ['required', 'numeric'],
            'tipo_de_movimiento' => ['required', 'string', 'in:Licencia,Fallecido,Renuncia'],
        ];
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
