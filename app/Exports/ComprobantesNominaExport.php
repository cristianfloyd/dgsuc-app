<?php

namespace App\Exports;

use NumberFormatter;
use App\Models\ComprobanteNominaModel;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class ComprobantesNominaExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithEvents, WithCustomStartCell, ShouldAutoSize, WithDrawings, WithColumnFormatting
{

    protected $nroLiqui;
    protected $descLiqui;
    protected $totalImporte;



    protected function formatMoneda(float $monto): string
    {
        $formatter = new NumberFormatter('es_AR', NumberFormatter::CURRENCY);
        return $formatter->format($monto);
    }


    public function __construct(int $nroLiqui, string $descLiqui)
    {
        $this->nroLiqui = $nroLiqui;
        $this->descLiqui = $descLiqui;
        $this->totalImporte = ComprobanteNominaModel::where('nro_liqui', $nroLiqui)->sum('importe');
    }


    public function drawings()
    {
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo UBA');
        $drawing->setPath(public_path('images/uba.png'));
        $drawing->setHeight(110);
        $drawing->setOffsetY(10);
        $drawing->setCoordinates('A1');

        // Calculamos el centro de la página A4
        $pageWidth = 595.28; // Ancho A4 en puntos
        $logoWidth = $drawing->getWidth();
        $offsetX = ($pageWidth - $logoWidth) / 2;

        // Centramos el logo
        $drawing->setOffsetX($offsetX);

        return $drawing;
    }

    protected function getImporteEnLetras(): string
    {
        $formatter = new NumberFormatter('es', NumberFormatter::SPELLOUT);
        return mb_strtoupper($formatter->format($this->totalImporte, 2));
    }


    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                // Configuración de página A4
                $event->sheet->getPageSetup()
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);

                // Márgenes en centímetros
                $event->sheet->getPageMargins()
                    ->setTop(2)
                    ->setRight(2)
                    ->setLeft(2)
                    ->setBottom(2);



                // Espacio para logo y márgenes
                $event->sheet->mergeCells('A1:B6');
                $event->sheet->getRowDimension(1)->setRowHeight(100);
                // Título
                $event->sheet->mergeCells('A7:B7');
                $event->sheet->setCellValue('A7', "Orden de Pago de Descuento {$this->descLiqui} Nro: {$this->nroLiqui}");

                // Altura de la fila para el texto largo
                $event->sheet->getRowDimension(8)->setRowHeight(100);

                // El texto se ajuste y sea visible
                $event->sheet->getStyle('A8:B8')->getAlignment()->setWrapText(true);

                // Texto descriptivo con importe
                $event->sheet->mergeCells('A8:B8');
                $event->sheet->setCellValue(
                    'A8',
                    'Visto las novedades informadas por las dependencias en el mes de noviembre del corriente, ' .
                        'se procedió a la liquidación de haberes arrojando la orden de pago presupuestaria y el informe ' .
                        'gerencial que se adjuntan a la presente, totalizando un importe de descuentos de PESOS ' .
                        $this->getImporteEnLetras() . ' ($' . number_format($this->totalImporte, 2, ',', '.') . ')'
                );

                // Texto final
                $event->sheet->mergeCells('A9:B9');
                $event->sheet->setCellValue('A9', 'Los mismos corresponden a los siguientes beneficiarios:');
            }
        ];
    }
    public function startCell(): string
    {
        return 'A12'; // Posición inicial de la tabla
    }

    public function collection()
    {
        return ComprobanteNominaModel::where('nro_liqui', $this->nroLiqui)
            ->select(
                'descripcion_retencion as Descripción',
                'importe as Importe'
            )
            ->get();
    }

    public function headings(): array
    {
        return [
            'Descripción',
            'Importe'
        ];
    }

    public function title(): string
    {
        return $this->descLiqui;
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_CURRENCY_USD
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Sin bordes
        $sheet->getStyle($sheet->calculateWorksheetDimension())
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);

        // Configuración de página A4
        $sheet->getPageSetup()
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
            ->setFitToWidth(1)
            ->setFitToHeight(0);


        // Ajustamos la altura de las filas del logo
        $sheet->getRowDimension(1)->setRowHeight(20);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(20);
        $sheet->getRowDimension(4)->setRowHeight(20);


        return [
            7 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => 'center']
            ],
            8 => [
                'alignment' => [
                    'wrapText' => true,
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'width' => 25
            ],
            9 => [
                'alignment' => ['horizontal' => 'left'],
                'font' => ['italic' => true]
            ],
            // Estilo para los encabezados de columna
            11 => [
                'font' => ['bold' => true],
                'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN]],
            ],
            'A' => [
                'alignment' => ['horizontal' => 'left'],
                'width' => 60
            ],
            'B' => [
                'alignment' => ['horizontal' => 'right'],
                'numberFormat' => ['formatCode' => '[$$-es-AR] #,##0.00;[Red]([$$-es-AR] #,##0.00)'],
                'width' => 25
            ]
        ];
    }
}
