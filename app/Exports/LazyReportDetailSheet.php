<?php

namespace App\Exports;

use Illuminate\Support\LazyCollection;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LazyReportDetailSheet implements
    FromCollection,
    WithMapping,
    WithHeadings,
    WithStyles,
    ShouldAutoSize,
    WithTitle
{
    protected $query;
    protected $lazyCollection;
    protected $columns;

    public function __construct($query)
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

        $this->lazyCollection = LazyCollection::make(function () {
            foreach ($this->query->cursor() as $record) {
                yield $record;
            }
        });
    }

    public function collection()
    {
        return $this->lazyCollection;
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

            switch ($column) {
                case 'cuil':
                    if (strlen($value) >= 3) {
                        $value = substr($value, 2, -1);
                    }
                    break;

                case 'impp_conce':
                    $value = is_numeric($value) ? $value : 0;
                    break;

                case 'nro_liqui':
                case 'nro_legaj':
                    $value = (string)$value;
                    break;
            }

            $mappedRow[] = $value;
        }
        return $mappedRow;
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

            // Estilo para columnas numéricas (importe)
            $sheet->getStyle('I2:I' . $lastRow)->applyFromArray([
                'numberFormat' => [
                    'formatCode' => '#,##0.00',
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ]);
        }

        // Filas alternadas
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

        // Congelar primera fila
        $sheet->freezePane('A2');

        return [];
    }

    public function title(): string
    {
        return 'Reporte de Conceptos';
    }
}
