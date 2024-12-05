<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmbargoSummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(protected Builder $query) {}

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->query
            ->select('codn_conce', DB::raw('SUM(importe_descontado) as total'))
            ->groupBy('codn_conce')
            ->orderBy('codn_conce')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Concepto',
            'Importe'
        ];
    }

    public function title(): string
    {
        return 'Totales por Concepto';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('B')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A:B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [
            1 => ['font' => ['bold' => true], 'background' => ['argb' => 'FFE5E5E5']],
        ];
    }
}
