<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use App\Exports\Sheets\BaseExcelSheet;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class EmbargoUacadSummary extends BaseExcelSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithColumnFormatting
{

    public function __construct(protected Builder $query)
    {}
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->query->getModel()
            ->newQuery()
            ->where('session_id', session()->getId())
            ->select('codc_uacad', DB::raw('SUM(importe_descontado) as total'))
            ->groupBy('codc_uacad')
            ->orderBy('codc_uacad')
            ->get();
    }

    public function headings(): array
    {
        return [
            'U. Acad.',
            'Total'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => '"$"#,##0.00',
        ];
    }

    public function title(): string
    {
        return 'Totales por Uacad';
    }

    public function styles(Worksheet $sheet)
    {
        parent::styles($sheet);

        $sheet->getStyle('A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return $this;
    }
}
