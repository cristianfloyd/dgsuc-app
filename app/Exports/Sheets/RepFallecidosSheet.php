<?php

namespace App\Exports\Sheets;

use Carbon\Carbon;
use App\Models\RepFallecido;
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
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RepFallecidosSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    protected string $periodo;

    public function __construct(string $periodo)
    {
        $this->periodo = $periodo;
    }

    public function query()
    {
        return RepFallecido::query()
            ->orderBy('nro_legaj');
    }

    public function title(): string
    {
        return 'Fallecidos';
    }

    public function headings(): array
    {
        return [
            [
                'Legajo',
                'Apellido',
                'Nombre',
                'CUIL',
                'Unidad Académica',
                'Fecha Defunción',
            ]
        ];
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function map($row): array
    {
        return [
            $row->nro_legaj,
            trim($row->apellido),
            trim($row->nombre),
            $row->cuil,
            $row->codc_uacad,
            $row->fec_defun?->format('d/m/Y'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            2 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Título en A1
                $event->sheet->mergeCells('A1:F1');
                $event->sheet->setCellValue('A1', 'Período: ' . substr($this->periodo, 0, 4) . '/' . substr($this->periodo, 4, 2));
                $event->sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Headers en A2
                $event->sheet->getStyle('A2:F2')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'CCCCCC'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Datos desde A3
                $lastRow = $event->sheet->getHighestRow();
                if ($lastRow > 3) {
                    $event->sheet->getStyle('A3:F' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);
                }
            },
        ];
    }
}
