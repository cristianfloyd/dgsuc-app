<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;

    protected ?string $heading = '';

    #[\Override]
    public function getColumns(): int|array
    {
        return 3;
    }
}
