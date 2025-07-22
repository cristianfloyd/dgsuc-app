<?php

namespace App\Exports;

use App\Exports\Sheets\AportesyContribucionesSummary;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdenAportesyContribuciones extends OrdenesDescuentoExport implements WithMultipleSheets
{
    public function styles(Worksheet $sheet)
    {
        parent::styles($sheet);


        $sheet->setCellValue('A1', 'APORTES Y CONTRIBUCIONES - ' . now()->format('d/m/Y'));


        $sheet->getStyle('A1')->getFont()->setSize(16);
        $sheet->getStyle('M:N')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => 'center']],
            2 => ['font' => ['bold' => true], 'background' => ['argb' => 'FFE5E5E5']],
        ];
    }

    public function sheets(): array
    {
        return [
            new AportesyContribucionesSummary($this->query),
            $this,
        ];
    }

    public function title(): string
    {
        return 'Aportes y Contribuciones';
    }
}
