<?php

namespace App\Exports\Sicoss;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ResumenDependenciasExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    protected Collection $data;
    protected int $year;
    protected int $month;

    public function __construct(Collection $data, int $year, int $month)
    {
        $this->data = $data;
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Código Dependencia',
            'Caracter',
            'Diferencia Aportes',
            'Diferencia Contribuciones',
        ];
    }

    public function map($row): array
    {
        return [
            $row->codc_uacad,
            $row->caracter,
            number_format($row->diferencia_aportes, 2, ',', '.'),
            number_format($row->diferencia_contribuciones, 2, ',', '.'),

        ];
    }

    public function title(): string
    {
        return "Diferencias por Dependencia {$this->year}-{$this->month}";
    }
}