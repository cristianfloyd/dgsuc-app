<?php

namespace App\Exports\Sheets;

use App\Models\Reportes\DosubaSinLiquidarModel;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DosubaSinLiquidarDataSheet implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithColumnFormatting,
    WithProperties,
    WithEvents,
    WithTitle
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    // Propiedades del documento
    public function properties(): array
    {
        return [
            'creator' => 'S.U.C. - Reportes',
            'title' => 'Reporte Dosuba Sin Liquidar',
            'description' => 'Listado de personal sin liquidar',
            'company' => 'UBA',
        ];
    }

    // Estilos de las celdas
    public function styles(Worksheet $sheet)
    {
        // Solo aplicamos estilos al encabezado y un borde ligero a los datos
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        $styles = [
            // Estilos para el encabezado
            1 => [
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
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
            // Borde para todos los datos
            "A1:$lastColumn$lastRow" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                    'outline' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                    ],
                ],
            ],
        ];

        // Filas alternadas para mejor legibilidad
        for ($row = 2; $row <= $lastRow; $row++) {
            if ($row % 2 == 0) {
                $styles["A$row:$lastColumn$row"] = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F2F2F2'], // Gris claro
                    ],
                ];
            }
        }

        return $styles;
    }

    public function title(): string
    {
        return '2 Meses';
    }

    // Formato de columnas específicas
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER, // Legajo
            'D' => '@',                         // CUIL como texto
            'F' => NumberFormat::FORMAT_NUMBER, // Última liquidación
        ];
    }

    public function query()
    {
        // Convertir la colección a query
        return DosubaSinLiquidarModel::whereIn('id', $this->records->pluck('id'));
    }

    public function headings(): array
    {
        return [
            'Legajo',
            'Apellido',
            'Nombre',
            'CUIL',
            'Unidad Académica',
            'Última Liquidación',
            'Período',
        ];
    }

    public function map($row): array
    {
        return [
            $row->nro_legaj,
            mb_convert_case($row->apellido, \MB_CASE_TITLE, 'UTF-8'),
            mb_convert_case($row->nombre, \MB_CASE_TITLE, 'UTF-8'),
            $this->formatCuil($row->cuil),
            $row->codc_uacad,
            $row->ultima_liquidacion,
            $row->periodo_fiscal,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                try {
                    $lastRow = $event->sheet->getHighestRow();
                    $lastColumn = $event->sheet->getHighestColumn();

                    // Configurar filtro automático
                    $event->sheet->setAutoFilter("A1:{$lastColumn}1");

                    // Ajustar altura de la fila de encabezado
                    $event->sheet->getRowDimension(1)->setRowHeight(30);

                    // Configurar selección por defecto
                    $event->sheet->getDelegate()->setSelectedCells("A1:{$lastColumn}$lastRow");

                    // Configurar vista de hoja
                    $event->sheet->getDelegate()->getSheetView()
                        ->setZoomScale(100)
                        ->setZoomScaleNormal(100);

                    // Fijar panel superior
                    $event->sheet->freezePane('A2');

                    // Permitir selección de filas
                    $event->sheet->getStyle("A1:{$lastColumn}$lastRow")->getProtection()
                        ->setLocked(false)
                        ->setHidden(false);
                } catch (\Exception $e) {
                    Log::error('Error en configuración Excel: ' . $e->getMessage());
                }
            },
        ];
    }

    private function formatCuil($cuil)
    {
        return preg_replace('/^(\d{2})(\d{8})(\d{1})$/', '$1-$2-$3', $cuil);
    }
}
