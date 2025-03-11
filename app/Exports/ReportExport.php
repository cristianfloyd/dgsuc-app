<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class ReportExport implements
    FromQuery,
    WithHeadings,
    WithStrictNullComparison,
    WithStyles,
    ShouldAutoSize,
    WithMapping,
    WithTitle,
    WithMultipleSheets
{
    use Exportable;

    protected $query;
    protected $columns;
    protected $summaryData;

    public function __construct(Builder $query)
    {
        $this->query = $query;
        $this->columns = [
            'nro_liqui' => 'Número',
            'desc_liqui' => 'Liquidación',
            'apellido' => 'Apellido',
            'nombre' => 'Nombre',
            'cuil' => 'DNI',
            'nro_legaj' => 'Legajo',
            'nro_cargo' => 'Secuencia',
            'codc_uacad' => 'Dependencia',
            'codn_conce' => 'Concepto',
            'impp_conce' => 'Importe'
        ];

        // Preparar los datos de resumen
        $this->prepareSummaryData();
    }

    public function headings(): array
    {
        return array_values($this->columns);
    }

    public function map($row): array
    {
        $mappedRow = [];
        foreach (array_keys($this->columns) as $column) {
            $value = $row->{$column} ?? '';

            // Aplicar transformaciones específicas según el tipo de columna
            switch ($column) {
                case 'cuil':
                    // Procesar el CUIL: eliminar los primeros 2 dígitos y el último dígito
                    if (strlen($value) >= 3) {
                        $value = substr($value, 2, -1);
                    }
                    break;

                case 'impp_conce':
                    // Asegurar formato numérico para importes
                    $value = is_numeric($value) ? $value : 0;
                    break;

                case 'nro_liqui':
                case 'nro_legaj':
                    // Asegurar que los números se muestren como texto
                    $value = (string)$value;
                    break;
            }

            $mappedRow[] = $value;
        }
        return $mappedRow;
    }

    public function query()
    {
        return $this->query;
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para el encabezado
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Estilo para el cuerpo del reporte
        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 1) {
            $sheet->getStyle('A2:' . $sheet->getHighestColumn() . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Estilo para columnas numéricas (importe) - columna I (9)
            $sheet->getStyle('I2:I' . $lastRow)->applyFromArray([
                'numberFormat' => [
                    'formatCode' => '#,##0.00',
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ]);
        }

        // Filas alternadas para mejor legibilidad
        for ($row = 2; $row <= $lastRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':' . $sheet->getHighestColumn() . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F2F2F2'],
                    ],
                ]);
            }
        }

        // Congelar la primera fila
        $sheet->freezePane('A2');

        return [];
    }

    public function title(): string
    {
        return 'Reporte de Conceptos';
    }

    public function sheets(): array
    {
        return [
            'Reporte de Conceptos' => new ReportDetailSheet($this->query),
            'Resumen' => new ReportSummarySheet($this->summaryData)
        ];
    }

    protected function prepareSummaryData(): void
    {
        // Obtener los datos para el resumen
        $data = $this->query->get();

        // Calcular el total general
        $totalGeneral = $data->sum('impp_conce');

        // Calcular totales por dependencia
        $totalsByDependency = $data->groupBy('codc_uacad')
            ->map(function ($group) {
                return [
                    'dependencia' => $group->first()->codc_uacad,
                    'total' => $group->sum('impp_conce'),
                    'registros' => $group->count()
                ];
            })
            ->sortByDesc('total')
            ->values();

        $this->summaryData = [
            'totalGeneral' => $totalGeneral,
            'totalsByDependency' => $totalsByDependency,
            'totalRegistros' => $data->count()
        ];
    }
}
