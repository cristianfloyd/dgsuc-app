<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LazyReportSummarySheet implements
    FromCollection,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    protected $summaryData;

    public function __construct($summaryData)
    {
        $this->summaryData = $summaryData;
    }

    public function collection()
    {
        $collection = new Collection();

        // Agregar título
        $collection->push(['RESUMEN DEL REPORTE']);
        $collection->push([]);

        // Agregar total general
        $collection->push(['Total General:', number_format($this->summaryData['totalGeneral'], 2, ',', '.')]);
        $collection->push(['Total Registros:', number_format($this->summaryData['totalRegistros'], 0, ',', '.')]);
        $collection->push([]);

        // Agregar encabezados de dependencias
        $collection->push(['Dependencia', 'Total', 'Registros']);

        // Agregar datos por dependencia
        foreach ($this->summaryData['totalsByDependency'] as $dependency) {
            $collection->push([
                $dependency['dependencia'],
                number_format($dependency['total'], 2, ',', '.'),
                number_format($dependency['registros'], 0, ',', '.')
            ]);
        }

        return $collection;
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para el título principal
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
        ]);

        // Estilos para los totales generales
        $sheet->getStyle('A3:B4')->applyFromArray([
            'font' => ['bold' => true],
        ]);

        // Estilos para la tabla de dependencias
        $tableStart = 6;
        $lastRow = $sheet->getHighestRow();

        // Estilo para el encabezado de la tabla
        $sheet->getStyle("A{$tableStart}:C{$tableStart}")->applyFromArray([
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
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Estilo para el cuerpo de la tabla
        if ($lastRow > $tableStart) {
            // Bordes y alineación para toda la tabla
            $sheet->getStyle("A{$tableStart}:C{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
            ]);

            // Alineación para columnas numéricas
            $sheet->getStyle("B" . ($tableStart + 1) . ":C{$lastRow}")->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ]);

            // Filas alternadas
            for ($row = $tableStart + 1; $row <= $lastRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F2F2F2'],
                        ],
                    ]);
                }
            }
        }

        // Ajustar el ancho de las columnas
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);

        return [];
    }

    public function title(): string
    {
        return 'Resumen';
    }
}
