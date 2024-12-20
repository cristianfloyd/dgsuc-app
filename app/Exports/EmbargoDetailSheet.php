<?php

namespace App\Exports;

use App\Exports\EmbargoSummarySheet;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class EmbargoDetailSheet implements FromCollection, WithMapping, WithColumnFormatting, WithStyles, WithHeadings, WithCustomStartCell,  ShouldAutoSize, WithBackgroundColor, WithTitle
{
    public function __construct(protected Builder $query)
    {}

    public function collection()
    {
        return $this->query
            // ->orderBy('nro_legaj', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Legajo',
            'Cargo',
            'Nombre',
            'Unidad Acad',
            'Caratula',
            'Nro. Embargo',
            'Concepto',
            'Importe',
            'Novedad 2',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_GENERAL,
            'B' => NumberFormat::FORMAT_GENERAL,  // Legajo
            'C' => NumberFormat::FORMAT_GENERAL,  // Cargo
            'D' => '@',
            'E' => '@',
            'F' => '@',
            'G' => NumberFormat::FORMAT_GENERAL, // nro_embargo
            'H' => NumberFormat::FORMAT_GENERAL, // Concepto
            'I' => '#,##0.00'  // Importe con 2 decimales
        ];
    }

    public function map($row): array
    {
        return [
            $row->nro_legaj,
            $row->nro_cargo,
            $row->nombre_completo,
            $row->codc_uacad,
            $row->caratula,
            $row->nro_embargo,
            $row->codn_conce,
            $row->importe_descontado,
            $row->nov2_conce
        ];
    }

    public function title(): string
    {
        return 'Reporte de Embargos';
    }

    public function startCell(): string
    {
        return 'B2';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'REPORTE DE EMBARGOS - ' . now()->format('d/m/Y'));

        // Aplicar filtros a los encabezados
        $lastColumn = 'O';
        $lastRow = $sheet->getHighestRow();
        $sheet->setAutoFilter("B2:{$lastColumn}{$lastRow}");

        $sheet->getStyle('A1')->getFont()->setSize(16);
        $sheet->getStyle('E')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => 'center']],
            2 => ['font' => ['bold' => true], 'background' => ['argb' => 'FFE5E5E5']],
        ];
    }

    public function backgroundColor()
    {
        return 'CCCCCC';
    }
}
