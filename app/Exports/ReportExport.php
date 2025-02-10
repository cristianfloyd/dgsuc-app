<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class ReportExport implements FromQuery, WithHeadings, WithStrictNullComparison
{
    use Exportable;
    protected $query;
    protected $collection;
    protected $type;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function headings(): array
    {
        return [
            'numero',
            'Liquidacion',
            'Apellido',
            'Nombre',
            'legajo',
            'Dependencia',
            'Concepto',
            'Importe'
        ];
    }
    public function array(): array
    {
        return $this->query->get()->toArray();
    }

    public function query()
    {
        return $this->query;
    }

}
