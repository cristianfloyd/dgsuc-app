<?php

namespace App\Exports;

use App\Exports\OrdenesDescuentoSheet200;
use App\Exports\OrdenesDescuentoSheet300;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class OrdenesDescuentoMultipleExport implements WithMultipleSheets
{
    use Exportable;
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function sheets(): array
    {
        return [
            new OrdenesDescuentoSheet200($this->query->clone()->whereBetween('codn_conce', [200, 299])),
            new OrdenesDescuentoSheet300($this->query->clone()->whereBetween('codn_conce', [300, 399]))
        ];
    }
}
