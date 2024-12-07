<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithProperties;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use App\Models\Reportes\DosubaSinLiquidarModel;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

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
            'creator'        => 'S.U.C. - Reportes',
            'title'         => 'Reporte Dosuba Sin Liquidar',
            'description'   => 'Listado de personal sin liquidar',
            'company'       => 'UBA',
        ];
    }

    // Estilos de las celdas
    public function styles(Worksheet $sheet)
    {
        // Solo aplicamos estilos al encabezado y un borde ligero a los datos
        $lastRow = $sheet->getHighestRow();
        Log::info("Last row: $lastRow");
        $lastColumn = $sheet->getHighestColumn();
        $styles = [
            // Estilos para el encabezado
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '91bde1']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER
                ],
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
            // Borde ligero solo para los datos
            "A2:G$lastRow" => [
                'borders' => [
                    'outline' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                    ],
                ],
            ],
        ];

        for ($row = 2; $row <= $lastRow; $row++) {
            if ($row % 1 == 0) {
                $styles["A$row:$lastColumn$row"] = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9D9D9'] // Gris al 35%
                    ]
                ];
            }
        }

        return $styles;
    }

    public function title(): string
    {
        return 'Datos';
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

    private function formatCuil($cuil)
    {
        return preg_replace('/^(\d{2})(\d{8})(\d{1})$/', '$1-$2-$3', $cuil);
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
            'Período'
        ];
    }

    public function map($row): array
    {
        return [
            $row->nro_legaj,
            mb_convert_case($row->apellido, MB_CASE_TITLE, 'UTF-8'),
            mb_convert_case($row->nombre, MB_CASE_TITLE, 'UTF-8'),
            $this->formatCuil($row->cuil),
            $row->codc_uacad,
            $row->ultima_liquidacion,
            $row->periodo_fiscal
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                try {
                    // $lastRow = $event->sheet->getHighestRow() + 2;
                    $lastRow = $event->sheet->getHighestRow();
                    $event->sheet->setAutoFilter('A1:G1');

                    // Configurar selección por defecto
                    $event->sheet->getDelegate()->setSelectedCells("A1:G$lastRow");

                    // Configurar vista de hoja
                    $event->sheet->getDelegate()->getSheetView()
                        ->setZoomScale(100)
                        ->setZoomScaleNormal(100);

                    // Mostrar líneas de cuadrícula usando el método correcto
                    $event->sheet->getDelegate()->setShowGridlines(true);

                    // Permitir selección de filas
                    $event->sheet->getStyle('A1:G'.$lastRow)->getProtection()
                        ->setLocked(false)
                        ->setHidden(false);

                } catch (\Exception $e) {
                    Log::error('Error en configuración Excel: ' . $e->getMessage());
                }

            },
        ];
    }
}
