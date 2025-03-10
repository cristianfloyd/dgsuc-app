<?php

namespace App\Exports\Sheets;

use Carbon\Carbon;
use App\Models\Reportes\Bloqueos;
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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class FallecidosBloqueoSheet implements
    FromQuery,
    WithTitle,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithEvents,
    WithCustomStartCell,
    WithColumnFormatting
{
    protected string $periodo;

    public function __construct(string $periodo)
    {
        $this->periodo = $periodo;
    }

    public function query()
    {
        return Bloqueos::query()
            ->porTipo('fallecido')
            ->with('legajo')
            ->orderBy('nro_legaj');
    }

    public function title(): string
    {
        return 'Fallecidos Bloqueos';
    }

    public function headings(): array
    {
        return [
            ['Fallecidos ingresados por bloqueos y aún no están en el sistema mapuche - Período: ' . substr($this->periodo, 0, 4) . '/' . substr($this->periodo, 4, 2)],
            [''], // Línea en blanco
            [
                'Legajo',
                'Apellido',
                'Nombre',
                'CUIL',
                'Unidad Académica',
                'Fecha Baja',
            ]
        ];
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function map($row): array
    {
        // Si tenemos la relación con el legajo, usamos esos datos
        if ($row->legajo) {
            return [
                $row->nro_legaj,
                trim($row->legajo->desc_appat), // Apellido desde Dh01
                trim($row->legajo->desc_nombr), // Nombre desde Dh01
                $row->legajo->cuil, // CUIL desde Dh01
                $row->dependencia,
                $row->fecha_baja?->format('d/m/Y'),
            ];
        }
        // Si no tenemos la relación, usamos los datos del bloqueo
        else {
            // Intentar separar el nombre completo en apellido y nombre
            $nombreCompleto = $row->nombre ?? '';
            $apellido = '';
            $nombre = '';

            // Si hay un espacio, asumimos que el formato es "Apellido Nombre"
            if (strpos($nombreCompleto, ' ') !== false) {
                $partes = explode(' ', $nombreCompleto, 2);
                $apellido = trim($partes[0]);
                $nombre = trim($partes[1] ?? '');
            } else {
                $apellido = $nombreCompleto;
            }

            return [
                $row->nro_legaj,
                $apellido,
                $nombre,
                $row->datos_validacion['cuil'] ?? '', // Intentamos obtener el CUIL de datos_validacion
                $row->dependencia,
                $row->fecha_baja?->format('d/m/Y'),
            ];
        }
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo base para todas las celdas
        $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 10
            ]
        ]);

        // Combinar celdas para el título del período
        $sheet->mergeCells('A1:F1');

        // Obtener la última fila
        $lastRow = $sheet->getHighestRow();

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E1F2']
                ],
            ],
            3 => [  // La fila de encabezados ahora es la 3
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
            // Borde para todos los datos
            "A3:F$lastRow" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER, // Legajo
            'D' => '@',                         // CUIL como texto
            'E' => '@',                         // UACAD como texto
            'F' => NumberFormat::FORMAT_DATE_DDMMYYYY // Fecha
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $lastRow = $sheet->getHighestRow();

                // Altura de las filas
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(3)->setRowHeight(30);

                // Filas alternadas para mejor legibilidad
                for ($row = 4; $row <= $lastRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle("A$row:F$row")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F2F2F2'] // Gris claro
                            ]
                        ]);
                    }
                }

                // Fijar panel superior
                $sheet->freezePane('A4');

                // Configurar vista de hoja
                $sheet->getDelegate()->getSheetView()
                    ->setZoomScale(100)
                    ->setZoomScaleNormal(100);
            }
        ];
    }
}
