<?php

namespace App\Exports\Sicoss;

use Illuminate\Support\Collection;

class ConceptosExport extends BaseSicossExport
{
    public function __construct(Collection $data, int $year, int $month)
    {
        parent::__construct($data, $year, $month, 'Reporte de Conceptos por Período');
    }

    public function headings(): array
    {
        return [
            'Código',
            'Descripción',
            'Importe',
            'Fecha de Control'
        ];
    }

    public function map($row): array
    {
        return [
            $row->codn_conce,
            $row->desc_conce,
            $row->importe,
            $row->created_at->format('d/m/Y H:i:s')
        ];
    }
}
