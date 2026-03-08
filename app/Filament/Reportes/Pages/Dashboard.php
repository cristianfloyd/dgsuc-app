<?php

namespace App\Filament\Reportes\Pages;

use Filament\Pages\Dashboard as Page;

class Dashboard extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';

    protected string $view = 'filament.reportes.pages.dashboard';

    public function getTitle(): string
    {
        return 'Panel de Reportes';
    }
}
