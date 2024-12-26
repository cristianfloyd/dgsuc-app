<?php

namespace App\Imports;

use App\Models\ImportData;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DataImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new ImportData([
            'column1' => $row['column1'],
            'column2' => $row['column2'],
            'column3' => $row['column3']
        ]);
    }
}
