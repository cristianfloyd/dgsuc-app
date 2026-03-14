<?php

namespace App\Exports;

use App\Exports\Sheets\FallecidosBloqueoSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FallecidosExport implements WithMultipleSheets
{
    public function __construct(protected $records, protected string $periodo) {}

    public function sheets(): array
    {
        return [
            'bloqueos' => new FallecidosBloqueoSheet($this->records, $this->periodo),
        ];
    }
}
