<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class DosubaSinLiquidarSummarySheet implements FromCollection, WithTitle, WithStyles, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    protected $records;
    protected string $periodo;

    public function __construct(Collection $records, string $periodo)
    {
        $this->records = $records;
        $this->periodo = $periodo;
    }

    protected function formatPeriodo(): string
    {
        return substr($this->periodo, 0, 4) . '/' . substr($this->periodo, 4, 2);
    }

    public function collection()
    {
        return new Collection([
            // Encabezado
            ['REPORTE DOSUBA LEGAJOS SIN LIQUIDAR', ''],
            ['Período:', $this->formatPeriodo()],
            ['Fecha de generación:', now()->format('d/m/Y H:i:s')],
            ['Sector:', 'DG del Sistema Universitario de Computación'],
            ['', ''], // Línea en blanco

            // Headings
            ['Concepto', 'Cantidad'],

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

            // Pie de página
            ['', ''],
            ['Generado por:', auth()->user()?->name ?? 'Sistema'],
            ['Período:', $this->formatPeriodo()],
            ['Versión:', config('app.version', '1.0')],
        ]);
    }

    public function title(): string
    {
        return 'Resumen';
    }

    public function styles(Worksheet $sheet)
    {
        // Obtener la última fila
        $lastRow = $sheet->getHighestRow();

        // Estilos base
        $styles = [
            // Estilo para título principal y período
            'A1:B2' => [
                'font' => [
                    'bold' => true, 'size' => 14,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1d3557']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER
                ],
                'borders' => [
                    'bottom' => ['borderStyle' => Border::BORDER_THIN],
                ]
            ],

            // Estilo para información de encabezado
            'A3:A5' => [
                'font' => ['bold' => true],
            ],

            // Estilo para pie de página
            'A'.($lastRow-2).':A'.$lastRow => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F0F0F0'] // Gris claro
                ],
            ],

            // Estilo del encabezado
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '91bde1']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER
                ],
            ],
        ];

        // Aplicar estilo a los títulos de sección
        $sectionTitles = ['A6', 'A8', 'A32', 'A35'];
        foreach ($sectionTitles as $cell) {
            $styles[$cell] = [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'bdd7ed']
                ],
            ];
        }

        // Borde para toda la tabla
        $styles["A1:B$lastRow"] = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        // Alineación para columna de cantidades
        $styles["B2:B$lastRow"] = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Combinar celdas para el título y período
                $event->sheet->mergeCells('A1:B1');
                $event->sheet->mergeCells('A2:B2');

                // Ajustar altura de las filas
                $event->sheet->getRowDimension(1)->setRowHeight(30);
                $event->sheet->getRowDimension(2)->setRowHeight(25);

                // Agregar bordes al pie de página
                $lastRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle('A'.($lastRow-2).':B'.$lastRow)->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
            }
        ];
    }
}
