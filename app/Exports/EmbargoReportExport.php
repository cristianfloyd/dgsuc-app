<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EmbargoReportExport implements WithMultipleSheets
{
    public function __construct(protected Builder $query)
    {}

    public function sheets(): array
    {
        return [
            new EmbargoDetailSheet($this->query),
            new EmbargoSummarySheet($this->query),
            new EmbargoUacadSummary($this->query)
        ];
    }

}
