<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;

    protected ?string $heading = '';

    public function getColumns(): int | string | array
    {
        return 3;
    }
}
