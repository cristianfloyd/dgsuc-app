<?php

namespace App\Exports\Sicoss;

use Illuminate\Support\Collection;

class AportesDiferenciasExport extends BaseSicossExport
{
    public function __construct(Collection $data, int $year, int $month)
    {
        parent::__construct($data, $year, $month, 'Reporte de Diferencias de Aportes');
    }

    public function title(): string
    {
        return 'Diferencias Aportes';
    }

    public function headings(): array
    {
        return [
            'Legajo',
            'CUIL',
            'Código Actividad',
            'Aportes SIJP DH21',
            'Aportes INSSJP DH21',
            'Aportes SIJP SICOSS',
            'Aportes INSSJP SICOSS',
            'Total Aportes DH21',
            'Total Aportes SICOSS',
            'Diferencia',
        ];
    }

    public function map($row): array
    {
        return [
            $row->dh01->nro_legaj ?? '',
            $row->cuil,
            $row->mapucheSicoss->cod_act ?? '',
            $row->aportesijpdh21,
            $row->aporteinssjpdh21,
            $row->aportesijp,
            $row->aporteinssjp,
            $row->aportesijpdh21 + $row->aporteinssjpdh21,
            $row->aportesijp + $row->aporteinssjp,
            $row->diferencia,
        ];
    }
}
