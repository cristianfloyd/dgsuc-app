<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\ImportDataModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DataImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $fechaBaja = null;

        // Procesamiento de fecha de baja según el tipo
        if (in_array(strtolower($row['tipo']), ['fallecimiento', 'renuncia']) && isset($row['fecha_baja'])) {
            $fechaBaja = Carbon::parse($row['fecha_baja']);

            // Si el día es 1, retrocedemos al último día del mes anterior
            if ($fechaBaja->day === 1) {
                $fechaBaja = $fechaBaja->subMonth()->endOfMonth();
            }
        }


        return new ImportDataModel([
            'fecha_registro' => Carbon::now(),
            'email' => $row['email'],
            'nombre' => $row['nombre'],
            'usuario_mapuche' => $row['usuario_mapuche'],
            'dependencia' => $row['dependencia'],
            'nro_legaj' => $row['nro_legaj'],
            'nro_cargo' => $row['nro_cargo'],
            'fecha_baja' => $fechaBaja,
            'tipo' => $row['tipo'],
            'observaciones' => $row['observaciones'] ?? null,
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
            'email' => ['required', 'email'],
            'nombre' => ['required', 'string'],
            'usuario_mapuche' => ['required', 'string'],
            'dependencia' => ['required', 'string'],
            'nro_legaj' => ['required', 'numeric'],
            'nro_cargo' => ['required', 'numeric'],
            'tipo' => ['required', 'string', 'in:licencia,fallecimiento,renuncia'],
            'fecha_baja' => ['required_if:tipo,fallecimiento,renuncia', 'date'],
        ];
    }

    /**
     * Mensajes personalizados para las validaciones
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            'email.required' => 'El campo email es obligatorio',
            'email.email' => 'El email debe tener un formato válido',
            'nombre.required' => 'El campo nombre es obligatorio',
            'usuario_mapuche.required' => 'El usuario Mapuche es obligatorio',
            'dependencia.required' => 'La dependencia es obligatoria',
            'nro_legaj.required' => 'El número de legajo es obligatorio',
            'nro_legaj.numeric' => 'El número de legajo debe ser numérico',
            'nro_cargo.required' => 'El número de cargo es obligatorio',
            'nro_cargo.numeric' => 'El número de cargo debe ser numérico',
            'tipo.required' => 'El tipo es obligatorio',
            'tipo.in' => 'El tipo debe ser: licencia, fallecimiento o renuncia',
            'fecha_baja.required_if' => 'La fecha de baja es obligatoria para fallecimiento o renuncia',
            'fecha_baja.date' => 'La fecha de baja debe ser una fecha válida'
        ];
    }
}
