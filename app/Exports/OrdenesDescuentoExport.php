<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class OrdenesDescuentoExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting, WithStyles, WithCustomStartCell, WithBackgroundColor, WithTitle
{
    use Exportable;

    protected $query;

    public function __construct($query)
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
            'Nro Liqui',
            'Descripción',
            'UA',
            'Dependencia',
            'Función',
            'Carácter',
            'Tipo Escalafón',
            'Fuente',
            'Inciso',
            'Programa',
            'Concepto',
            'Descripción Concepto',
            'Importe',
            'Última Actualización'
        ];
    }

    public function map($row): array
    {
        return [
            $row->nro_liqui,
            $row->desc_liqui,
            $row->codc_uacad,
            $row->desc_item,
            $row->codn_funci,
            $row->caracter,
            $row->tipoescalafon,
            $row->codn_fuent,
            $row->nro_inciso,
            $row->codn_progr,
            $row->codn_conce,
            $row->desc_conce,
            $row->impp_conce,
            $row->last_sync
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_GENERAL,
            'B' => '@',
            'C' => '@',
            'D' => '@',
            'E' => NumberFormat::FORMAT_GENERAL,
            'F' => '@',
            'G' => '@',
            'H' => '@',
            'I' => '@',
            'J' => '@',
            'K' => NumberFormat::FORMAT_GENERAL,
            'L' => '@',
            'M' => '#,##0.00',
            'N' => '#,##0.00',
            'O' => NumberFormat::FORMAT_DATE_DDMMYYYY
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A1', 'REPORTE DE ÓRDENES DE DESCUENTO - ' . now()->format('d/m/Y'));

        // Aplicar filtros a los encabezados
        $lastColumn = 'O';
        $lastRow = $sheet->getHighestRow();
        $sheet->setAutoFilter("B2:{$lastColumn}{$lastRow}");

        $sheet->getStyle('A1')->getFont()->setSize(16);
        $sheet->getStyle('M:N')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => 'center']],
            2 => ['font' => ['bold' => true], 'background' => ['argb' => 'FFE5E5E5']],
        ];
    }

    public function startCell(): string
    {
        return 'B2';
    }

    public function backgroundColor()
    {
        return 'CCCCCC';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 30,
            'C' => 10,
            'D' => 30,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 30,
            'M' => 15,
            'N' => 15,
            'O' => 20,
        ];
    }

    public function title(): string
    {
        return 'Orden Descuentos';
    }
}

