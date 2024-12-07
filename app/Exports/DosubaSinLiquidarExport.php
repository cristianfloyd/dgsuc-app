<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
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

class DosubaSinLiquidarExport implements WithMultipleSheets
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function sheets(): array
    {
        return [
            new DosubaSinLiquidarSummarySheet($this->records),
            new DosubaSinLiquidarDataSheet($this->records),
        ];
    }
}
