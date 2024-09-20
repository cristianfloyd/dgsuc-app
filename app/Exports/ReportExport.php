<?php

namespace App\Exports;

use Filament\Notifications\Collection;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;

class ReportExport implements FromQuery
{
    use Exportable;
    protected $export;

    public function __construct(Builder $query)
    {
        $this->export = $query;
    }

    public function array(): array
    {
        return $this->export->get()->toArray();
    }

    public function query()
    {
        return $this->export;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = $this->query()->get();
        return $data;
    }
}
