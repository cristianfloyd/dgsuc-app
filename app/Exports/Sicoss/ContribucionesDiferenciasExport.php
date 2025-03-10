<?php

namespace App\Exports\Sicoss;

use Illuminate\Support\Collection;

class ContribucionesDiferenciasExport extends BaseSicossExport
{
    public function __construct(Collection $data, int $year, int $month)
    {
        parent::__construct($data, $year, $month, 'Reporte de Diferencias de Contribuciones');
    }

    public function headings(): array
    {
        return [
            'CUIL',
            'Contribución SIJP DH21',
            'Contribución INSSJP DH21',
            'Contribución SIJP',
            'Contribución INSSJP',
            'Diferencia'
        ];
    }

    public function map($row): array
    {
        return [
            $row->cuil,
            $row->contribucionsijpdh21,
            $row->contribucioninssjpdh21,
            $row->contribucionsijp,
            $row->contribucioninssjp,
            $row->diferencia
        ];
    }
}
