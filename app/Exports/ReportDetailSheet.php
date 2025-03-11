<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class ReportDetailSheet implements
    FromQuery,
    WithHeadings,
    WithStrictNullComparison,
    WithStyles,
    ShouldAutoSize,
    WithMapping,
    WithTitle
{
    protected $query;
    protected $columns;

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
    }

    public function headings(): array
    {
        return array_values($this->columns);
    }

    public function map($row): array
    {
        $mappedRow = [];
        foreach (array_keys($this->columns) as $column) {
            if ($column === 'cuil') {
                // Procesar el CUIL: eliminar los primeros 2 dígitos y el último dígito
                $fullCuil = $row->{$column} ?? '';
                if (strlen($fullCuil) >= 3) {
                    // Extraer solo la parte central del CUIL
                    $mappedRow[] = substr($fullCuil, 2, -1);
                } else {
                    $mappedRow[] = $fullCuil;
                }
            } else {
                $mappedRow[] = $row->{$column} ?? '';
            }
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
}
