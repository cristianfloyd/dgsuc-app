<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithBackgroundColor;

class OrdenesDescuentoSheet200 extends OrdenesDescuentoExport implements WithBackgroundColor
{
    public function title(): string
    {
        return 'Conceptos 200-299';
    }
}
