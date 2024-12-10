<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class OrdenesDescuentoSheet300 extends OrdenesDescuentoExport
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

    public function title(): string
    {
        return 'Conceptos 300-399';
    }
}
