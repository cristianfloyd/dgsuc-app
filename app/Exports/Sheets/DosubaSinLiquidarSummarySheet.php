<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class DosubaSinLiquidarSummarySheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected $records;

    public function __construct(Collection $records)
    {
        $this->records = $records; // #1d2554
    }

    public function collection()
    {
        return new Collection([
            // Totales generales
            ['Total de registros', $this->records->count()],
            [''],  // Línea en blanco para separación

            // Agrupación por Unidad Académica
            ['Distribución por Unidad Académica'],
            ...$this->records->groupBy('codc_uacad')
                ->map(fn($group, $uacad) => [$uacad, $group->count()])
                ->values(),
            [''],

            // Última liquidación
            ['Distribución por Última Liquidación'],
            ...$this->records->groupBy('ultima_liquidacion')
                ->map(fn($group, $liq) => [$liq, $group->count()])
                ->sortByDesc(fn($item) => $item[0])
                ->take(5)
                ->values(),
            [''],

            // Período Fiscal
            ['Distribución por Período'],
            ...$this->records->groupBy('periodo_fiscal')
                ->map(fn($group, $periodo) => [$periodo, $group->count()])
                ->sortByDesc(fn($item) => $item[0])
                ->values(),
        ]);
    }

    public function title(): string
    {
        return 'Resumen';
    }

    public function headings(): array
    {
        return ['Concepto', 'Cantidad'];
    }

    public function styles(Worksheet $sheet)
    {
        // Obtener la última fila
        $lastRow = $sheet->getHighestRow();

        // Estilos base
        $styles = [
            // Estilo del encabezado
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '91bde1']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                ],
            ],
            // Estilo para títulos de sección
            'A1:B1' => [
                'borders' => [
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                ]
            ],
        ];

        // Aplicar estilo a los títulos de sección
        $sectionTitles = ['A1', 'A4', 'A8', 'A12'];
        foreach ($sectionTitles as $cell) {
            $styles[$cell] = [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'bdd7ed']
                ],
            ];
        }

        // Borde para toda la tabla
        $styles['A1:B'.$lastRow] = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        // Alineación para columna de cantidades
        $styles['B2:B'.$lastRow] = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
            ],
        ];

        return $styles;
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
