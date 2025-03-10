<?php

namespace App\Exports\Sicoss;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;

abstract class BaseSicossExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithStyles,
    WithCustomStartCell,
    ShouldAutoSize
{
    protected Collection $data;
    protected int $year;
    protected int $month;
    protected string $reportTitle;

    public function __construct(Collection $data, int $year, int $month, string $reportTitle)
    {
        $this->data = $data;
        $this->year = $year;
        $this->month = $month;
        $this->reportTitle = $reportTitle;
    }

    public function collection()
    {
        return $this->data;
    }

    public function title(): string
    {
        return 'Reporte';
    }

    public function startCell(): string
    {
        return 'A6'; // Comenzar los datos en la fila 6 para dejar espacio para el encabezado
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para el título principal
        $sheet->mergeCells('A1:' . $this->getLastColumn() . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('A1', $this->reportTitle);

        // Estilo para el período fiscal
        $sheet->mergeCells('A2:' . $this->getLastColumn() . '2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('A2', "Período Fiscal: {$this->year}-" . str_pad($this->month, 2, '0', STR_PAD_LEFT));

        // Estilo para la fecha de generación
        $sheet->mergeCells('A3:' . $this->getLastColumn() . '3');
        $sheet->getStyle('A3')->getFont()->setSize(10);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('A3', "Generado el: " . now()->format('d/m/Y H:i:s'));

        // Espacio en blanco
        $sheet->mergeCells('A4:' . $this->getLastColumn() . '4');

        // Estilo para los encabezados de columnas
        $headingsRow = 5;
        $headingStyle = $sheet->getStyle('A' . $headingsRow . ':' . $this->getLastColumn() . $headingsRow);
        $headingStyle->getFont()->setBold(true);
        $headingStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
        $headingStyle->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $headingStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Estilo para los datos
        $dataFirstRow = $headingsRow + 1;
        $dataLastRow = $headingsRow + $this->data->count();
        if ($dataLastRow >= $dataFirstRow) {
            $dataRange = 'A' . $dataFirstRow . ':' . $this->getLastColumn() . $dataLastRow;
            $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            // Estilo para filas alternas
            for ($i = $dataFirstRow; $i <= $dataLastRow; $i++) {
                if ($i % 2 == 0) {
                    $sheet->getStyle('A' . $i . ':' . $this->getLastColumn() . $i)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F5F5F5');
                }
            }
        }

        return [];
    }

    /**
     * Obtiene la última columna basada en la cantidad de encabezados
     */
    protected function getLastColumn(): string
    {
        $headingsCount = count($this->headings());
        return $this->getColumnLetter($headingsCount);
    }

    /**
     * Convierte un número de columna a letra (1 = A, 2 = B, etc.)
     */
    protected function getColumnLetter(int $columnNumber): string
    {
        $columnLetter = '';
        while ($columnNumber > 0) {
            $modulo = ($columnNumber - 1) % 26;
            $columnLetter = chr(65 + $modulo) . $columnLetter;
            $columnNumber = (int)(($columnNumber - $modulo) / 26);
        }
        return $columnLetter;
    }

    abstract public function headings(): array;
    abstract public function map($row): array;
}
