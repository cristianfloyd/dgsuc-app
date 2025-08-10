<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BloqueosResultadosExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $records;

    public function __construct(Collection $records)
    {
        $this->records = $records;
    }

    public function collection()
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Email',
            'usuario_mapuche',
            'Nro. Cargo',
            'Legajo',
            'Tipo Bloqueo',
            'Fecha Baja',
            'Estado',
            'UACAD',
            'Observaciones',
            'Mensaje Error',
            'Procesado',
        ];
    }

    public function map($record): array
    {
        // Formatear la fecha correctamente si es una instancia de Carbon o DateTime
        $fechaBaja = '';
        if (!empty($record->fecha_baja)) {
            if ($record->fecha_baja instanceof \Carbon\Carbon || $record->fecha_baja instanceof \DateTime) {
                $fechaBaja = $record->fecha_baja->format('Y-m-d');
            } else {
                $fechaBaja = $record->fecha_baja; // Ya es un string
            }
        }

        return [
            $record->nombre ?? '',
            $record->email ?? '',
            $record->usuario_mapuche ?? '',
            $record->nro_cargo ?? '',
            $record->nro_legaj ?? '',
            $record->tipo ?? '',
            $fechaBaja,
            $record->estado->value ?? $record->estado ?? '',
            $record->cargo->codc_uacad ?? '',
            $record->observaciones ?? '',
            $record->mensaje_error ?? '',
            $record->esta_procesado ? 'Sí' : 'No',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A:G' => ['alignment' => ['horizontal' => 'center']],
        ];
    }
}
