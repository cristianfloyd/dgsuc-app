<?php

namespace App\Exports\Sicoss;

use Illuminate\Support\Collection;

class CuilsDiferenciasExport extends BaseSicossExport
{
    public function __construct(Collection $data, int $year, int $month)
    {
        parent::__construct($data, $year, $month, 'Reporte de Diferencias de CUILs');
    }

    public function headings(): array
    {
        return [
            'CUIL',
            'Origen',
            'Fecha de Control',
        ];
    }

    public function map($row): array
    {
        return [
            $row->cuil,
            $row->origen,
            $row->fecha_control,
        ];
    }
}
