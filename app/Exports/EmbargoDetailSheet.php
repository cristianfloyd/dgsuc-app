<?php

namespace App\Exports;

use App\Exports\EmbargoSummarySheet;
use App\Exports\Sheets\BaseExcelSheet;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class EmbargoDetailSheet extends BaseExcelSheet implements
    FromCollection,
    WithMapping,
    WithColumnFormatting,
    WithHeadings,
    ShouldAutoSize,
    WithBackgroundColor,
    WithTitle,
    WithColumnWidths,
    WithCustomStartCell
{
    /**
     * @var Builder $query Consulta para obtener los datos
     */
    protected Builder $query;

    /**
     * @var string $periodoLiquidacion Período de liquidación del reporte
     */
    protected string $periodoLiquidacion;

    /**
     * Constructor
     *
     * @param Builder $query Consulta para obtener los datos
     * @param string $periodoLiquidacion Período de liquidación (opcional)
     */
    public function __construct(Builder $query, string $periodoLiquidacion = '')
    {
        $this->query = $query;
        $this->periodoLiquidacion = $periodoLiquidacion ?: date('Y-m');
    }

    /**
     * Define la celda de inicio para los datos
     *
     * @return string
     */
    public function startCell(): string
    {
        return 'A6';
    }

    /**
     * Obtiene la colección de datos para la hoja de Excel
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->query
            // ->orderBy('nro_legaj', 'asc')
            ->get();
    }

    /**
     * Define los encabezados de las columnas
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Legajo',
            'Cargo',
            'Nombre',
            'U. Acad',
            'Caratula',
            'Embargo',
            'Cpto.',
            'Importe',
            'Nov. 1',
            'Nov. 2',
            'Remunerativo',
            '860',
            '861'
        ];
    }

    /**
     * Define el ancho de las columnas específicas
     *
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 10,  // Legajo
            'B' => 8,   // Cargo
            'C' => 30,  // Nombre
            'D' => 12,  // Unidad Acad
            'E' => 30,  // Caratula (limitada a 30 caracteres)
            'F' => 15,  // Nro. Embargo
            'G' => 10,  // Concepto
            'H' => 15,  // Importe
            'I' => 15,  // Novedad 1
            'J' => 15,  // Novedad 2
            'K' => 15,  // Remunerativo
            'L' => 15,  // Concepto 860
            'M' => 15,  // Concepto 861
        ];
    }

    /**
     * Define el formato de las columnas
     *
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_GENERAL,
            'B' => NumberFormat::FORMAT_GENERAL,  // Legajo
            'C' => NumberFormat::FORMAT_GENERAL,  // Cargo
            'D' => '@',
            'E' => '@',
            'F' => '@',
            'G' => NumberFormat::FORMAT_GENERAL, // nro_embargo
            'H' => NumberFormat::FORMAT_GENERAL, // Concepto
            'I' => '#,##0.00',  // Importe con 2 decimales
            'J' => '#,##0.00',  // Novedad 1 con 2 decimales
            'K' => '#,##0.00',  // Novedad 2 con 2 decimales
            'L' => '#,##0.00',  // Remunerativo con 2 decimales
            'M' => '#,##0.00',  // Concepto 860 con 2 decimales
            'N' => '#,##0.00',  // Concepto 861 con 2 decimales
        ];
    }

    /**
     * Mapea cada fila de datos a las columnas de Excel
     * Limita el texto de la caratula a 30 caracteres
     *
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        // Limitar el texto de la caratula a 30 caracteres
        $caratula = $row->caratula;
        if (strlen($caratula) > 30) {
            $caratula = substr($caratula, 0, 27) . '...';
        }

        return [
            $row->nro_legaj,
            $row->nro_cargo,
            $row->nombre_completo,
            $row->codc_uacad,
            $caratula, // Caratula limitada a 30 caracteres
            $row->nro_embargo,
            $row->codn_conce,
            $row->importe_descontado,
            $row->nov1_conce ?? 0,
            $row->nov2_conce ?? 0,
            $row->remunerativo ?? 0,
            $row->{'860'} ?? 0,
            $row->{'861'} ?? 0
        ];
    }

    /**
     * Define el título de la hoja
     *
     * @return string
     */
    public function title(): string
    {
        return 'Reporte de Embargos';
    }

    /**
     * Aplica estilos a la hoja de Excel
     *
     * @param Worksheet $sheet
     * @return $this
     */
    public function styles(Worksheet $sheet)
    {
        parent::styles($sheet);

        // Aplicar estilos a los encabezados (ahora en la fila 6)
        $sheet->getStyle('A6:M6')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD'],
            ],
        ]);

        // Aplicar bordes a todas las celdas con datos (desde la fila 6)
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A6:M$lastRow")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);

        // Centrar algunas columnas (solo para los datos, desde la fila 6)
        $sheet->getStyle('A6:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D6:D' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F6:G' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Alinear a la derecha las columnas numéricas (solo para los datos, desde la fila 6)
        $sheet->getStyle('I6:N' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Mejorar la presentación de la columna Caratula
        $sheet->getStyle('E6:E' . $lastRow)->getAlignment()->setWrapText(true);
        $sheet->getStyle('E6:E' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

        // Mejorar la presentación de la columna Nombre
        $sheet->getStyle('C6:C' . $lastRow)->getAlignment()->setWrapText(true);

        // Aplicar colores alternados a las filas para mejorar la legibilidad (desde la fila 7)
        for ($row = 7; $row <= $lastRow; $row++) {
            if (($row - 6) % 2 == 0) {
                $sheet->getStyle('A' . $row . ':N' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F9F9F9'],
                    ],
                ]);
            }
        }

        // FORMATO CONDICIONAL: Resaltar embargos con montos altos (más de 50000)
        $conditionalStyles = [
            new \PhpOffice\PhpSpreadsheet\Style\Conditional(),
            new \PhpOffice\PhpSpreadsheet\Style\Conditional(),
            new \PhpOffice\PhpSpreadsheet\Style\Conditional(),
        ];


        // IMPLEMENTACIÓN DE FILTROS AUTOMÁTICOS (solo para la fila de encabezados)
        $lastColumn = 'M';
        $sheet->setAutoFilter('A6:' . $lastColumn . '6');

        // Congelar la fila 6 para que los encabezados permanezcan visibles al desplazarse
        $sheet->freezePane('A7');

        // MEJORA: Agregar información de cabecera en las primeras 5 filas
        // Título del reporte con estilo mejorado
        $sheet->setCellValue('A1', "REPORTE DE EMBARGOS - PERÍODO {$this->periodoLiquidacion}");
        $sheet->mergeCells('A1:M1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '1F497D'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'DCE6F1'],
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '4F81BD'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Descripción del reporte
        $sheet->setCellValue('A2', 'Reporte detallado de embargos con información de conceptos adicionales');
        $sheet->mergeCells('A2:M2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 10,
                'color' => ['rgb' => '1F497D'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Información de la organización
        $sheet->setCellValue('A3', 'Universidad de Buenos Aires');
        $sheet->mergeCells('A3:C3');
        $sheet->getStyle('A3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
            ],
        ]);

        // Información de generación del reporte (en dos columnas)
        $sheet->setCellValue('A4', 'Fecha de generación:');
        $sheet->setCellValue('B4', now()->format('d/m/Y H:i'));
        $sheet->getStyle('A4:B4')->applyFromArray([
            'font' => [
                'size' => 10,
            ],
        ]);

        $sheet->setCellValue('D4', 'Generado por:');
        $sheet->setCellValue('E4', auth()->guard('web')->user()->name ?? 'Sistema Automatizado');
        $sheet->getStyle('D4:E4')->applyFromArray([
            'font' => [
                'size' => 10,
            ],
        ]);

        // Información adicional
        $sheet->setCellValue('A5', 'Categoría:');
        $sheet->setCellValue('B5', 'Reportes Financieros');
        $sheet->setCellValue('D5', 'Período:');
        $sheet->setCellValue('E5', $this->periodoLiquidacion);
        $sheet->getStyle('A5:E5')->applyFromArray([
            'font' => [
                'size' => 10,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '4F81BD'],
                ],
            ],
        ]);

        // Aplicar un borde inferior a toda la sección de encabezado
        $sheet->getStyle('A5:M5')->applyFromArray([
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '4F81BD'],
                ],
            ],
        ]);

        return $this;
    }

    /**
     * Define el color de fondo para los encabezados
     * Ya no se utiliza este método porque estamos aplicando el estilo directamente
     *
     * @return string
     */
    public function backgroundColor()
    {
        return 'CCCCCC';
    }
}
