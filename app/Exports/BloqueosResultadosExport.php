<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BloqueosResultadosExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected Collection $resultados;

    public function __construct(Collection $resultados)
    {
        $this->resultados = $resultados;
    }

    public function collection()
    {
        return $this->resultados;
    }

    public function headings(): array
    {
        return [
            'Nro. Cargo',
            'Legajo',
            'Tipo Bloqueo',
            'Fecha Proceso',
            'Estado',
            'Resultado',
            'Información Adicional'
        ];
    }

    public function map($resultado): array
    {
        return [
            $resultado['cargo_id'] ?? '',
            $resultado['legajo'] ?? '',
            $resultado['tipo'] ?? '',
            $resultado['fecha_proceso'] ?? '',
            $resultado['success'] ? 'Exitoso' : 'Fallido',
            $resultado['message'] ?? '',
            is_array($resultado['metadata']) ? json_encode($resultado['metadata']) : ''
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
