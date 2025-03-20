<?php

namespace App\Exports;

use App\Exports\EmbargoSummarySheet;
use App\Exports\Sheets\BaseExcelSheet;
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

class EmbargoDetailSheet extends BaseExcelSheet implements FromCollection, WithMapping, WithColumnFormatting, WithHeadings, ShouldAutoSize, WithBackgroundColor, WithTitle
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
            '861'
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
            'I' => '#,##0.00',  // Importe con 2 decimales
            'K'  => '#,##0.00',  // Importe con 2 decimales
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
            $row->nov2_conce,
            $row->{'861'}
        ];
    }

    public function title(): string
    {
        return 'Reporte de Embargos';
    }



    public function styles(Worksheet $sheet)
    {
        parent::styles($sheet);

        // Estilos específicos para esta hoja si son necesarios
        return $this;
    }

    public function backgroundColor()
    {
        return 'CCCCCC';
    }
}
