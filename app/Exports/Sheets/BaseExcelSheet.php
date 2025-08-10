<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\{Alignment, Border, Color, Fill};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class BaseExcelSheet implements WithStyles, WithCustomStartCell
{
    public function startCell(): string
    {
        return 'A6'; // Comenzar en A6 para dejar espacio para el encabezado
    }

    public function styles(Worksheet $sheet)
    {
        $this->applyCommonStyles($sheet);

        return $this;
    }

    protected function applyHeader(Worksheet $sheet): void
    {
        // Fusionar celdas para el título
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'UNIVERSIDAD DE BUENOS AIRES');

        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', 'REPORTE DE EMBARGOS');

        $sheet->mergeCells('A3:E3');
        $sheet->setCellValue('A3', 'Fecha: ' . now()->format('d/m/Y'));

        // Aplicar estilos al encabezado
        $sheet->getStyle('A1:E3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Estilo específico para el título
        $sheet->getStyle('A1')->getFont()->setSize(16);

        // Color de fondo para el nombre de la institución
        $sheet->getStyle('A1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->setStartColor(new Color('2C3E50'));
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');

        // Línea separadora
        $sheet->getStyle('A4:Z4')->getBorders()
            ->getBottom()
            ->setBorderStyle(Border::BORDER_MEDIUM)
            ->setColor(new Color('2C3E50'));
    }

    protected function applyCommonStyles(Worksheet $sheet): void
    {
        // Primero aplicar el encabezado
        $this->applyHeader($sheet);

        // Estilo para las cabeceras de datos (que ahora comienzan en la fila 6)
        $sheet->getStyle('A6:Z6')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2C3E50'],
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

        // Estilo para el contenido
        $sheet->getStyle('A7:Z' . $sheet->getHighestRow())->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);

        // Alternar colores de fila para mejor legibilidad
        for ($row = 7; $row <= $sheet->getHighestRow(); $row++) {
            if ($row % 2 == 0) {
                $color = new Color('F8F9FA');
                $sheet->getStyle('A' . $row . ':Z' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->setStartColor($color);
            }
        }

        // Auto-ajustar ancho de columnas
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
