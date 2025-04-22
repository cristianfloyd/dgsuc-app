<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Exports\Sheets\RepFallecidosSheet;
use App\Exports\Sheets\FallecidosBloqueoSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FallecidosExport implements WithMultipleSheets
{
    protected $records;
    protected string $periodo;

    public function __construct($records, string $periodo)
    {
        $this->records = $records;
        $this->periodo = $periodo;
    }

    public function sheets(): array
    {
        return [
            'bloqueos' => new FallecidosBloqueoSheet($this->records, $this->periodo)
        ];
    }
}
