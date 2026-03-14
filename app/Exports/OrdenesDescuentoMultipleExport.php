<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class OrdenesDescuentoMultipleExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(protected $query) {}

    public function sheets(): array
    {
        return [
            new OrdenesDescuentoSheet200($this->query->clone()->whereBetween('codn_conce', [200, 299])),
            new OrdenAportesyContribuciones($this->query->clone()->whereBetween('codn_conce', [300, 399])),
        ];
    }
}
