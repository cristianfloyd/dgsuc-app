<?php

namespace App\Exports;

use App\Models\AfipMapucheArt;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class AfipMapucheArtExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithTitle,
    ShouldAutoSize,
    WithStyles,
    WithColumnFormatting,
    WithEvents
{
    protected $periodo_fiscal;
    protected $query;

    public function __construct(string $periodo_fiscal, $query)
    {
        $this->periodo_fiscal = $periodo_fiscal;
        $this->query = $query;
    }

    public function query()
    {
        $query = $this->query;
        return $query;
    }

    public function headings(): array
    {
        return [
            'CUIL',
            'Apellido y Nombre',
            'Nacimiento',
            'Sueldo',
            'Sexo',
            'Establecimiento',
            'Tarea',
        ];
    }

    public function map($row): array
    {
        return [
            $row->cuil,
            $row->apellido_y_nombre,
            $row->nacimiento?->format('d/m/Y'),
            $row->sueldo,
            $row->sexo,
            $row->dh30?->desc_item,
            $row->tarea,
        ];
    }

    public function title(): string
    {
        return "AFIP ART {$this->periodo_fiscal}";
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para el encabezado
            1 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA'],
                ],
            ],
            // Estilo para todas las celdas
            'A2:G1000' => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,          // CUIL
            'C' => NumberFormat::FORMAT_DATE_DDMMYYYY, // Nacimiento
            'D' => NumberFormat::FORMAT_CURRENCY_USD, // Sueldo
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Obtener la última columna y fila
                $lastColumn = $event->sheet->getHighestColumn();
                $lastRow = $event->sheet->getHighestRow();

                // Agregar filtros
                $event->sheet->setAutoFilter('A1:' . $lastColumn . '1');

                // Ajustar el ancho de las columnas
                foreach (range('A', $lastColumn) as $column) {
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // Agregar bordes a toda la tabla
                $event->sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
