<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\RepEmbarazada;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RepEmbarazadasExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithStyles
{
    /**
     * Retorna la query para la exportación.
     */
    public function query()
    {
        return RepEmbarazada::query()
            ->orderBy('nro_legaj');
    }

    /**
     * Define el mapeo de cada fila.
     *
     * @param RepEmbarazada $embarazada
     */
    public function map($embarazada): array
    {
        return [
            $embarazada->nro_legaj,
            trim($embarazada->apellido), // Removemos el padding de CHAR(20)
            trim($embarazada->nombre),   // Removemos el padding de CHAR(20)
            $embarazada->cuil,
            $embarazada->codc_uacad,
        ];
    }

    /**
     * Define los encabezados del Excel.
     */
    public function headings(): array
    {
        return [
            'Legajo',
            'Apellido',
            'Nombre',
            'CUIL',
            'Unidad Académica',
        ];
    }

    /**
     * Personaliza los estilos del Excel.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA']
                ]
            ],
        ];
    }
}
