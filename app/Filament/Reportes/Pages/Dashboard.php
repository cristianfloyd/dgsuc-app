<?php

namespace App\Filament\Reportes\Pages;

use Filament\Pages\Dashboard as Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.reportes.pages.dashboard';

    public function getTitle(): string
    {
        return 'Panel de Reportes';
    }
}
