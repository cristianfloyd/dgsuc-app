<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use App\Exports\Sheets\RepEmbarazadasSheet;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithProperties;
use App\Models\Reportes\DosubaSinLiquidarModel;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Exports\Sheets\DosubaSinLiquidarDataSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use App\Exports\Sheets\DosubaSinLiquidarSummarySheet;
use App\Exports\Sheets\RepFallecidosSheet;

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
            'fallecidos' => new RepFallecidosSheet($this->periodo),
        ];
    }
}
