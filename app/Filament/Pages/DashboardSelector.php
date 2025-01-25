<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Enums\IconPosition;

class DashboardSelector extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $title = 'Seleccionar Panel';
    protected static ?string $navigationLabel = 'Inicio';

    protected static string $view = 'filament.pages.dashboard-selector';

    protected function getPanels(): array
    {
        return [
            [
                'name' => 'Panel Principal',
                'icon' => 'heroicon-o-building-office',
                'url' => '/admin',
                'description' => 'Gestión general del sistema'
            ],
            [
                'name' => 'Panel de Reportes',
                'icon' => 'heroicon-o-document-chart-bar',
                'url' => '/reports',
                'description' => 'Generación y visualización de informes'
            ],
        ];
    }
}
