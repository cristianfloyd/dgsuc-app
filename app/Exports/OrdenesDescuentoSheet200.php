<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Override;

class OrdenesDescuentoSheet200 extends OrdenesDescuentoExport implements WithBackgroundColor
{
    #[Override]
    public function title(): string
    {
        return 'Conceptos 200-299';
    }
}
