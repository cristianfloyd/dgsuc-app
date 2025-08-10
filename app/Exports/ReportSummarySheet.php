<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportSummarySheet implements
    FromCollection,
    WithTitle,
    WithStyles,
    ShouldAutoSize,
    WithStrictNullComparison,
    WithCustomStartCell
{
    protected $summaryData;

    public function __construct(array $summaryData)
    {
        $this->summaryData = $summaryData;
    }

    public function collection()
    {
        $collection = new Collection();

        // Agregar encabezado para el resumen general
        $collection->push(['RESUMEN GENERAL']);
        $collection->push(['Total de registros:', $this->summaryData['totalRegistros']]);
        $collection->push(['Importe total:', $this->summaryData['totalGeneral']]);
        $collection->push([]);  // Fila vacía como separador

        // Agregar encabezado para el resumen por dependencia
        $collection->push(['RESUMEN POR DEPENDENCIA']);
        $collection->push(['Dependencia', 'Cantidad de registros', 'Importe total']);

        // Agregar datos por dependencia
        foreach ($this->summaryData['totalsByDependency'] as $dependencyData) {
            $collection->push([
                $dependencyData['dependencia'],
                $dependencyData['registros'],
                $dependencyData['total'],
            ]);
        }

        return $collection;
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para los títulos principales
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '000000'],
            ],
        ]);

        $sheet->getStyle('A5')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '000000'],
            ],
        ]);

        // Estilo para el encabezado de la tabla de dependencias
        $sheet->getStyle('A6:C6')->applyFromArray([
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

        // Estilo para los datos de resumen general
        $sheet->getStyle('A2:B3')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);

        // Formato para los valores monetarios
        $sheet->getStyle('B3')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('C7:C' . ($sheet->getHighestRow()))->getNumberFormat()->setFormatCode('#,##0.00');

        // Estilo para los datos de la tabla de dependencias
        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 6) {
            $sheet->getStyle('A7:C' . $lastRow)->applyFromArray([
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

            // Filas alternadas para mejor legibilidad
            for ($row = 7; $row <= $lastRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F2F2F2'],
                        ],
                    ]);
                }
            }
        }

        // Alineación de columnas
        $sheet->getStyle('B2:B3')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
        ]);

        $sheet->getStyle('B7:C' . $lastRow)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
        ]);

        return [];
    }

    public function title(): string
    {
        return 'Resumen';
    }
}
