<?php

namespace App\Exports;

use App\Exports\Sheets\DosubaSinLiquidarDataSheet;
use App\Exports\Sheets\DosubaSinLiquidarSummarySheet;
use App\Exports\Sheets\FallecidosBloqueoSheet;
use App\Exports\Sheets\RepEmbarazadasSheet;
use App\Exports\Sheets\RepFallecidosSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DosubaSinLiquidarExport implements WithMultipleSheets
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
            'summary' => new DosubaSinLiquidarSummarySheet($this->records, $this->periodo),
            'data' => new DosubaSinLiquidarDataSheet($this->records),
            'embarazadas' => new RepEmbarazadasSheet($this->periodo),
            // 'fallecidos' => new RepFallecidosSheet( $this->periodo),
            // 'fallecidos_bloqueo' => new FallecidosBloqueoSheet( $this->periodo),
        ];
    }
}
