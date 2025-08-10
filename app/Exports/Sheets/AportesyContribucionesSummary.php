<?php

namespace App\Exports\Sheets;

use App\Enums\ConceptoGrupo;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use NumberToWords\NumberToWords;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AportesyContribucionesSummary implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize, WithCustomStartCell, WithDrawings, WithBackgroundColor
{
    protected $query;

    protected $resumenDosuba;

    protected $resumenAfip;

    protected $resumenContribucionesAfip;

    protected $resumenSeguroAfip;

    protected $resumenArtAfip;

    protected $resumenAportesAfip;

    public function __construct($query)
    {
        $this->query = $query;
        $this->resumenDosuba = $this->getResumenByConceptoGrupo(ConceptoGrupo::DOSUBA);

        $this->resumenContribucionesAfip = $this->getResumenByConceptoGrupo(ConceptoGrupo::CONTRIBUCIONES_AFIP);
        $this->resumenSeguroAfip = $this->getResumenByConceptoGrupo(ConceptoGrupo::SEGURO_CONTRIBUCION_AFIP);
        $this->resumenArtAfip = $this->getResumenByConceptoGrupo(ConceptoGrupo::ART_AFIP);
        $this->resumenAportesAfip = $this->getResumenByConceptoGrupo(ConceptoGrupo::APORTES_AFIP);
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function backgroundColor(): string
    {
        return 'FFFFFF';
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo UBA');
        $drawing->setPath(public_path('images/encabezado.png'));
        $drawing->setHeight(200);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetY(11);

        return $drawing;
    }

    public function collection(): Collection
    {
        // todas las liquidaciones unicas
        $liquidaciones = $this->query->clone()
            ->select('nro_liqui', 'desc_liqui')
            ->groupBy('nro_liqui', 'desc_liqui')
            ->orderBy('nro_liqui')
            ->get();

        // Preparamos los datos por liquidación
        $datosMatriz = $liquidaciones->map(function ($liquidacion) {
            return [
                'nro_liqui' => $liquidacion->nro_liqui,
                'desc_liqui' => $liquidacion->desc_liqui,
                'dosuba' => $this->getTotalPorLiquidacion($liquidacion->nro_liqui, ConceptoGrupo::DOSUBA),
                'contribuciones' => $this->getTotalPorLiquidacion($liquidacion->nro_liqui, ConceptoGrupo::CONTRIBUCIONES_AFIP),
                'seguro' => $this->getTotalPorLiquidacion($liquidacion->nro_liqui, ConceptoGrupo::SEGURO_CONTRIBUCION_AFIP),
                'art' => $this->getTotalPorLiquidacion($liquidacion->nro_liqui, ConceptoGrupo::ART_AFIP),
                'aportes' => $this->getTotalPorLiquidacion($liquidacion->nro_liqui, ConceptoGrupo::APORTES_AFIP),
            ];
        });

        // Calculamos los totales
        $totales = [
            'dosuba' => $this->resumenDosuba->sum('total_importe'),
            'contribuciones' => $this->resumenContribucionesAfip->sum('total_importe'),
            'seguro' => $this->resumenSeguroAfip->sum('total_importe'),
            'art' => $this->resumenArtAfip->sum('total_importe'),
            'aportes' => $this->resumenAportesAfip->sum('total_importe'),
        ];

        $totalGeneral = array_sum($totales);

        // Calculamos subtotales
        $totalDosuba = $this->resumenDosuba->sum('total_importe');
        $totalContribucionesAfip = $this->resumenContribucionesAfip->sum('total_importe');
        $totalSeguroAfip = $this->resumenSeguroAfip->sum('total_importe');
        $totalArtAfip = $this->resumenArtAfip->sum('total_importe');
        $totalAportesAfip = $this->resumenAportesAfip->sum('total_importe');

        $totalAfip = $totalContribucionesAfip + $totalSeguroAfip + $totalArtAfip + $totalAportesAfip;
        $totalGeneral = $totalDosuba + $totalAfip;

        $numberToWords = new NumberToWords();
        $currencyTransformer = $numberToWords->getCurrencyTransformer('es');
        $totalGeneralTexto = strtoupper($currencyTransformer->toWords($totalGeneral, 'ARS'));

        return collect([
            [''], // Fila 1 - Logo
            [''], // Fila 2 - Logo
            [''], // Fila 3 - Logo
            [''], // Fila 4 - Logo
            [''], // Fila 5 - Logo
            [''], // Fila 6 - Logo
            [''], // Fila 7 - Logo
            [''], // Fila 8 - Logo
            [''], // Fila 9 - Logo
            [''], // Fila 10 - Logo
            [''], // Fila 11 - Logo
            [''], // libre
            [''], // Línea libre
            [
                'Visto las novedades informadas por las dependencias en el mes de noviembre del corriente, se procedió a la liquidación de haberes arrojando la orden de pago presupuestaria y el informe gerencial que se adjuntan a la presente, totalizando un importe de aportes y contribuciones de ' . $totalGeneralTexto . ' ($ ' . number_format($totalGeneral, 2, ',', '.') . '.-)',
            ],
            [''],
            ['Los mismo corresponden a los siguientes beneficiarios,'],
            [''],
            [''],
            // ['DOSUBA - Resumen por Liquidación', '', ''],
            // ['Liquidación', 'Descripción', 'Importe Total'],
            // ...$this->resumenDosuba->map(fn($item) => [
            //     $item->nro_liqui,
            //     $item->desc_liqui,
            //     $item->total_importe
            // ]),
            // ['TOTAL DOSUBA', '', $totalDosuba],
            // ['', '', ''],
            // ['AFIP - Resumen por Liquidación', '', ''],
            // ['Descripción', 'Importe Total'],
            // ...$this->resumenAfip->map(fn($item) => [
            //     $item->desc_liqui,
            //     $item->total_importe
            // ]),
            // ['TOTAL AFIP', '',  $totalAfip],
            // ['', '', '', ''],
            // ['TOTAL GENERAL', '', $totalGeneral],
            ['', '', '', ''],
            ['Liquidación', 'DOSUBA', 'Contribuciones', 'Seguro', 'ART', 'Aportes', 'Sub Totales'],

            // Datos por liquidación
            ...$datosMatriz->map(fn ($row) => [
                $row['desc_liqui'],
                $row['dosuba'],
                $row['contribuciones'],
                $row['seguro'],
                $row['art'],
                $row['aportes'],
                array_sum(\array_slice($row, 2)), // Total por fila
            ]),

            // Totales
            ['TOTALES',
                $totales['dosuba'],
                $totales['contribuciones'],
                $totales['seguro'],
                $totales['art'],
                $totales['aportes'],
                $totalGeneral,
            ],
        ]);
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        // Configuración de página A4
        $sheet->getPageSetup()
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
            ->setFitToWidth(1)
            ->setFitToHeight(0);


        // Ajustamos la altura de las filas del logo
        $sheet->getRowDimension(1)->setRowHeight(15);
        $sheet->getRowDimension(2)->setRowHeight(15);
        $sheet->getRowDimension(3)->setRowHeight(15);
        $sheet->getRowDimension(4)->setRowHeight(15);
        $sheet->getRowDimension(5)->setRowHeight(15);
        $sheet->getRowDimension(6)->setRowHeight(15);
        $sheet->getRowDimension(7)->setRowHeight(15);
        $sheet->getRowDimension(8)->setRowHeight(15);
        $sheet->getRowDimension(9)->setRowHeight(15);
        $sheet->getRowDimension(10)->setRowHeight(15);
        $sheet->getRowDimension(11)->setRowHeight(15);
        $sheet->getRowDimension(14)->setRowHeight(80);

        // Combinamos las celdas para el texto introductorio
        $sheet->mergeCells('A14:G14');
        $sheet->mergeCells('A16:G16');

        // inicio de la tabla
        $inicioTabla = 19;
        $lastRow = $sheet->getHighestRow();


        $sheet->getStyle('A14')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A14:E14')->getAlignment()->setVertical('top');

        return [
            $inicioTabla => ['font' => ['bold' => true, 'size' => 14]],
            $inicioTabla + 1 => ['font' => ['bold' => true]],
            $lastRow => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'FFEB9C']],
            ],
            'B1:G' . $lastRow => [
                'numberFormat' => ['formatCode' => '#,##0.00'],
            ],
        ];
    }

    public function title(): string
    {
        return 'Resumen Estadístico';
    }

    private function getResumenByConceptoGrupo(ConceptoGrupo $grupo)
    {
        return $this->query->clone()
            ->reorder()
            ->whereIn('codn_conce', $grupo->getConceptos())
            ->groupBy('nro_liqui', 'desc_liqui')
            ->selectRaw('
                nro_liqui,
                desc_liqui,
                SUM(impp_conce) as total_importe
            ')
            ->orderBy('nro_liqui')
            ->get();
    }

    private function getTotalPorLiquidacion(int $nroLiqui, ConceptoGrupo $grupo): float
    {
        return $this->query->clone()
            ->where('nro_liqui', $nroLiqui)
            ->whereIn('codn_conce', $grupo->getConceptos())
            ->sum('impp_conce');
    }
}
