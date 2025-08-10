<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RepFallecidosSheet implements
    FromCollection,
    WithTitle,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithEvents,
    WithColumnFormatting
{
    protected string $periodo;

    protected $records;

    public function __construct($records, string $periodo)
    {
        $this->records = $records;
        $this->periodo = $periodo;
    }

    public function collection()
    {
        return $this->records;
    }

    public function title(): string
    {
        return 'Fallecidos';
    }

    public function headings(): array
    {
        return [
            ['Período: ' . substr($this->periodo, 0, 4) . '/' . substr($this->periodo, 4, 2)],
            [''], // Línea en blanco
            [
                'Legajo',
                'Apellido',
                'Nombre',
                'CUIL',
                'Unidad Académica',
                'Fecha Defunción',
            ],
        ];
    }

    public function map($row): array
    {
        dump($row);
        return [
            $row->nro_legaj,
            trim($row->apellido),
            trim($row->nombre),
            $row->cuil,
            $row->codc_uacad,
            $row->feccha_baja?->format('d/m/Y'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo base para todas las celdas
        $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 10,
            ],
        ]);

        // Combinar celdas para el título del período
        $sheet->mergeCells('A1:F1');

        // Obtener la última fila
        $lastRow = $sheet->getHighestRow();

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E1F2'],
                ],
            ],
            3 => [  // La fila de encabezados ahora es la 3
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Borde para todos los datos
            "A3:F$lastRow" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER, // Legajo
            'D' => '@',                         // CUIL como texto
            'E' => '@',                         // UACAD como texto
            'F' => NumberFormat::FORMAT_DATE_DDMMYYYY, // Fecha
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet;
                $lastRow = $sheet->getHighestRow();

                // Altura de las filas
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(3)->setRowHeight(30);

                // Filas alternadas para mejor legibilidad
                for ($row = 4; $row <= $lastRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle("A$row:F$row")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F2F2F2'], // Gris claro
                            ],
                        ]);
                    }
                }

                // Fijar panel superior
                $sheet->freezePane('A4');

                // Configurar vista de hoja
                $sheet->getDelegate()->getSheetView()
                    ->setZoomScale(100)
                    ->setZoomScaleNormal(100);
            },
        ];
    }
}
