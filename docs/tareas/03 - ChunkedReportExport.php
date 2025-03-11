<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ChunkedReportExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, WithStyles
{
    use Exportable;

    protected $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Liquidación',
            'Descripción',
            'Apellido',
            'Nombre',
            'CUIL',
            'Legajo',
            'Secuencia',
            'Dependencia',
            'Concepto',
            'Importe'
        ];
    }

    public function map($row): array
    {
        return [
            $row->nro_liqui,
            $row->desc_liqui,
            $row->apellido,
            $row->nombre,
            $row->cuil,
            $row->nro_legaj,
            $row->nro_cargo,
            $row->codc_uacad,
            $row->codn_conce,
            $row->impp_conce
        ];
    }

    public function chunkSize(): int
    {
        return 1000; // Procesar 1000 registros a la vez
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para la fila de encabezados
            1 => ['font' => ['bold' => true]],
        ];
    }
}