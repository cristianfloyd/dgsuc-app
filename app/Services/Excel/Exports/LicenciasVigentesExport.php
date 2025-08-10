<?php

namespace App\Services\Excel\Exports;

use App\Data\Responses\LicenciaVigenteData;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Spatie\LaravelData\DataCollection;

class LicenciasVigentesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    /**
     * @var DataCollection
     */
    protected DataCollection $licencias;

    /**
     * @var string
     */
    protected string $periodo;

    /**
     * @param DataCollection $licencias
     * @param string|null $periodo
     */
    public function __construct(DataCollection $licencias, ?string $periodo = null)
    {
        $this->licencias = $licencias;
        $this->periodo = $periodo ?? now()->format('Y-m');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->licencias->all());
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Legajo',
            'Tipo',
            'Cargo',
            'Condición',
            'Inicio Periodo',
            'Fin Periodo',
            'Días',
            'Fecha Desde',
            'Fecha Hasta',
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        // Usamos el método que ya tenemos en el DTO para convertir a Excel
        if ($row instanceof LicenciaVigenteData) {
            return $row->toExcelRow();
        }

        // Fallback por si no es una instancia correcta
        return [
            'Legajo' => $row->nro_legaj ?? '',
            'Tipo' => isset($row->es_legajo) ? ($row->es_legajo ? 'Legajo' : 'Cargo') : '',
            'Cargo' => $row->nro_cargo ?? '',
            'Condición' => isset($row->condicion) ? $this->getDescripcionCondicion($row->condicion) : '',
            'Inicio Periodo' => $row->inicio ?? '',
            'Fin Periodo' => $row->final ?? '',
            'Días' => $row->dias_totales ?? '',
            'Fecha Desde' => isset($row->fecha_desde) ? $row->fecha_desde->format('d/m/Y') : '',
            'Fecha Hasta' => isset($row->fecha_hasta) ? $row->fecha_hasta->format('d/m/Y') : 'Sin definir',
        ];
    }

    /**
     * Aplica estilos a la hoja de Excel.
     *
     * @param Worksheet $sheet
     *
     * @return void
     */
    public function styles(Worksheet $sheet): void
    {
        // Estilo para la cabecera
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4F81BD'],
            ],
        ]);

        // Estilo para el contenido
        $sheet->getStyle('A2:I' . ($this->collection()->count() + 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FFD9D9D9'],
                ],
            ],
        ]);
    }

    /**
     * Devuelve el título de la hoja.
     *
     * @return string
     */
    public function title(): string
    {
        return "Licencias Vigentes - Periodo {$this->periodo}";
    }

    /**
     * Obtiene la descripción legible de la condición.
     *
     * @param int $condicion
     *
     * @return string
     */
    protected function getDescripcionCondicion(int $condicion): string
    {
        return match ($condicion) {
            5 => 'Maternidad',
            10 => 'Excedencia',
            11 => 'Maternidad Down',
            12 => 'Vacaciones',
            13 => 'Licencia Sin Goce de Haberes',
            18 => 'ILT Primer Tramo',
            19 => 'ILT Segundo Tramo',
            51 => 'Protección Integral',
            default => 'Otra Licencia',
        };
    }
}
