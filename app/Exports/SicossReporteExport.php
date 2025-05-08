<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use App\Data\Responses\SicossReporteData;
use App\Data\Responses\SicossTotalesData;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Services\Reports\SicossReporteService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class SicossReporteExport implements WithMultipleSheets
{
    use Exportable;

    protected string $anio;
    protected string $mes;
    protected ?Collection $records;
    protected ?SicossTotalesData $totales;
    protected SicossReporteService $sicossReporteService;

    public function __construct(string $anio, string $mes, ?Collection $records = null, ?array $totales = null)
    {
        $this->anio = $anio;
        $this->mes = $mes;
        $this->records = $records;
        $this->totales = $totales ? SicossTotalesData::fromArray($totales) : null;
        $this->sicossReporteService = app(SicossReporteService::class);
    }

    public function sheets(): array
    {
        return [
            'Detalle' => new class($this->anio, $this->mes, $this->records, $this->sicossReporteService) implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting, WithStyles, WithCustomStartCell, WithBackgroundColor, WithTitle {
                use Exportable;

                protected string $anio;
                protected string $mes;
                protected ?Collection $records;
                protected SicossReporteService $sicossReporteService;

                public function __construct(string $anio, string $mes, ?Collection $records, SicossReporteService $sicossReporteService)
                {
                    $this->anio = $anio;
                    $this->mes = $mes;
                    $this->records = $records;
                    $this->sicossReporteService = $sicossReporteService;
                }

                public function collection(): Collection
                {
                    if ($this->records) {
                        return $this->records->map(fn($item) => SicossReporteData::fromModel($item));
                    }

                    return $this->sicossReporteService->getReporteData($this->anio, $this->mes);
                }

                public function headings(): array
                {
                    return [
                        'Nro Liquidación',
                        'Descripción',
                        'Remunerativo',
                        'No Remunerativo',
                        'Aportes SIJP',
                        'Aportes INSSJP',
                        'Contribución SIJP',
                        'Contribución INSSJP',
                    ];
                }

                public function map($row): array
                {
                    return [
                        $row->numeroLiquidacion,
                        $row->descripcionLiquidacion,
                        $row->remunerativo,
                        $row->noRemunerativo,
                        $row->aportesSijp,
                        $row->aportesInssjp,
                        $row->contribucionesSijp,
                        $row->contribucionesInssjp,
                    ];
                }

                public function columnFormats(): array
                {
                    return [
                        'A' => NumberFormat::FORMAT_GENERAL,
                        'B' => '@',
                        'C' => '#,##0.00',
                        'D' => '#,##0.00',
                        'E' => '#,##0.00',
                        'F' => '#,##0.00',
                        'G' => '#,##0.00',
                        'H' => '#,##0.00',
                        'I' => '#,##0.00',
                        'J' => '#,##0.00',
                    ];
                }

                public function styles(Worksheet $sheet)
                {
                    $sheet->mergeCells('A1:H1');
                    $sheet->setCellValue('A1', 'REPORTE SICOSS - PERÍODO ' . $this->anio . '/' . $this->mes);

                    // Aplicar filtros a los encabezados
                    $lastColumn = 'H';
                    $lastRow = $sheet->getHighestRow();
                    $sheet->setAutoFilter("A2:{$lastColumn}{$lastRow}");

                    $sheet->getStyle('A1')->getFont()->setSize(16);
                    $sheet->getStyle('C:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    return [
                        1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => 'center']],
                        2 => ['font' => ['bold' => true], 'background' => ['argb' => 'FFE5E5E5']],
                    ];
                }

                public function startCell(): string
                {
                    return 'A2';
                }

                public function backgroundColor()
                {
                    return 'CCCCCC';
                }

                public function title(): string
                {
                    return 'Detalle';
                }
            },
            'Totales' => new class($this->totales) implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithColumnFormatting, WithCustomStartCell, WithBackgroundColor, WithTitle {
                use Exportable;

                protected SicossTotalesData $totales;

                public function __construct(SicossTotalesData $totales)
                {
                    $this->totales = $totales;
                }

                public function collection(): Collection
                {
                    return new Collection([
                        [
                            'concepto' => 'Total Aportes',
                            'monto' => $this->totales->totalAportes,
                        ],
                        [
                            'concepto' => 'Total Contribuciones',
                            'monto' => $this->totales->totalContribuciones,
                        ],
                        [
                            'concepto' => 'Total Remunerativo Imponible',
                            'monto' => $this->totales->totalRemunerativo,
                        ],
                        [
                            'concepto' => 'Total No Remunerativo Imponible',
                            'monto' => $this->totales->totalNoRemunerativo,
                        ],
                    ]);
                }

                public function headings(): array
                {
                    return [
                        'Concepto',
                        'Monto',
                    ];
                }

                public function columnFormats(): array
                {
                    return [
                        'A' => '@',
                        'B' => '#,##0.00',
                    ];
                }

                public function styles(Worksheet $sheet)
                {
                    $sheet->mergeCells('A1:B1');
                    $sheet->setCellValue('A1', 'TOTALES DEL PERÍODO');

                    $sheet->getStyle('A1')->getFont()->setSize(16);
                    $sheet->getStyle('B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    return [
                        1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => 'center']],
                        2 => ['font' => ['bold' => true], 'background' => ['argb' => 'FFE5E5E5']],
                    ];
                }

                public function startCell(): string
                {
                    return 'A2';
                }

                public function backgroundColor()
                {
                    return 'CCCCCC';
                }

                public function title(): string
                {
                    return 'Totales';
                }
            },
        ];
    }
}
