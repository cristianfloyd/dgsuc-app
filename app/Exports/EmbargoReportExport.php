<?php

namespace App\Exports;

use App\Exports\{
    EmbargoDetailSheet,
    EmbargoSummarySheet,
    EmbargoUacadSummary
};
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EmbargoReportExport implements WithMultipleSheets, WithProperties
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

    public function properties(): array
    {
        return [
            'creator'        => config('app.name'),
            'title'         => 'Reporte de Embargos',
            'description'   => 'Reporte detallado de embargos',
            'company'       => config('app.name'),
            'category'      => 'Reportes',
            'manager'       => 'Sistema',
            'created'       => now()
        ];
    }
}
