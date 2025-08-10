<?php

namespace App\Exports;

use App\Data\Responses\SicossReporteData;
use App\Data\Responses\SicossTotalesData;
use App\Services\Reports\SicossReporteService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

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
            'Detalle' => new class ($this->anio, $this->mes, $this->records, $this->sicossReporteService) implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting, WithStyles, WithCustomStartCell, WithBackgroundColor, WithTitle {
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

                /**
                 * Obtiene la colección de datos para el reporte SICOSS.
                 *
                 * Si se proporcionan registros previamente, los transforma utilizando SicossReporteData.
                 * De lo contrario, recupera los datos del reporte a través del servicio SicossReporteService.
                 *
                 * @return Collection Colección de datos del reporte SICOSS
                 */
                public function collection(): Collection
                {
                    try {
                        if ($this->records) {
                            $records = $this->records->map(fn ($item) => SicossReporteData::fromModel($item));
                            return $records;
                        }

                        return $this->sicossReporteService->getReporteData($this->anio, $this->mes);
                    } catch (\Exception $e) {
                        Log::error('Error al obtener datos para el reporte SICOSS', [
                            'error' => $e->getMessage(),
                            'anio' => $this->anio,
                            'mes' => $this->mes,
                        ]);
                        return new Collection(); // Devolver colección vacía en caso de error
                    }
                }

                public function headings(): array
                {
                    return [
                        'Nro Liquidación',
                        'Descripción',
                        'Seguro',
                        'ART',
                        'Remunerativo',
                        'No Remunerativo',
                        'Aportes SIJP',
                        'Aportes INSSJP',
                        'Contribución SIJP',
                        'Contribución INSSJP',
                    ];
                }

                /**
                 * Mapea una fila de datos para el reporte SICOSS, transformando los valores de la fila en un array.
                 *
                 * Este método convierte cada registro en un array con los campos necesarios para el reporte,
                 * proporcionando valores predeterminados en caso de que algún campo sea nulo.
                 *
                 * @param mixed $row Fila de datos a mapear
                 *
                 * @return array Array con los valores mapeados de la fila
                 */
                public function map($row): array
                {
                    try {
                        return [
                            $row->numeroLiquidacion ?? '',
                            $row->descripcionLiquidacion ?? '',
                            $row->c305 ?? 0,
                            $row->c306 ?? 0,
                            $row->remunerativo ?? 0,
                            $row->noRemunerativo ?? 0,
                            $row->aportesSijp ?? 0,
                            $row->aportesInssjp ?? 0,
                            $row->contribucionesSijp ?? 0,
                            $row->contribucionesInssjp ?? 0,
                        ];
                    } catch (\Exception $e) {
                        Log::error('Error al mapear fila para el reporte SICOSS', [
                            'error' => $e->getMessage(),
                            'row' => json_encode($row),
                        ]);
                        // Devolver valores por defecto en caso de error
                        return ['', '', 0, 0, 0, 0, 0, 0, 0];
                    }
                }

                public function columnFormats(): array
                {
                    return [
                        'A' => NumberFormat::FORMAT_GENERAL,
                        'B' => '@',
                        'C' => NumberFormat::FORMAT_CURRENCY_USD,
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
            'Totales' => new class ($this->totales) implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithColumnFormatting, WithCustomStartCell, WithBackgroundColor, WithTitle {
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
                        [
                            'concepto' => 'Total Seguro',
                            'monto' => $this->totales->totalC305,
                        ],
                        [
                            'concepto' => 'Total ART',
                            'monto' => $this->totales->totalC306,
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
