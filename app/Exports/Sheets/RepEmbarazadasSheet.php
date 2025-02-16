<?php

declare(strict_types=1);

namespace App\Exports\Sheets;

use App\Models\RepEmbarazada;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class RepEmbarazadasSheet implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithStyles, WithTitle, WithEvents, WithColumnFormatting
{
    protected string $periodo;

    public function __construct(string $periodo)
    {
        $this->periodo = $periodo;
    }

    public function query()
    {
        return RepEmbarazada::query()->orderBy('nro_legaj');
    }

    public function map($embarazada): array
    {
        return [
            $embarazada->nro_legaj,
            trim($embarazada->apellido),
            trim($embarazada->nombre),
            $embarazada->cuil,
            $embarazada->codc_uacad,
        ];
    }

    public function headings(): array
    {
        return [
            ['Período: ' . $this->formatPeriodo()],
            [''], // Línea en blanco
            [
                'Legajo',
                'Apellido',
                'Nombre',
                'CUIL',
                'Unidad Académica',
            ]
        ];
    }

    /**
     * Formatea el período en el formato YYYY/MM
     *
     * @return string El período formateado (ej: "2024/03")
     */
    protected function formatPeriodo(): string
    {
        return substr($this->periodo, 0, 4) . '/' . substr($this->periodo, 4, 2);
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo base para todas las celdas
        $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 10
            ]
        ]);

        // Combinar celdas para el título del período
        $sheet->mergeCells('A1:E1');

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
            3 => [  // La fila de encabezados ahora es la 3
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER, // Legajo
            'D' => '@',                         // CUIL como texto
            'E' => '@'                          // UACAD como texto
        ];
    }

    public function title(): string
    {
        return 'Personal Embarazada';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();
                $range = 'A3:' . $lastColumn . $lastRow;

                // Aplicar bordes
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ],
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '000000']
                        ]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);

                // Altura de las filas
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(3)->setRowHeight(30);

                // Ajustar el ancho de las columnas
                foreach (range('A', $lastColumn) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // Fijar panel superior
                $sheet->freezePane('A4');
            }
        ];
    }
}
