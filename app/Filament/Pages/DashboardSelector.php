<?php

namespace App\Filament\Pages;

use App\Support\PanelRegistry;
use Filament\Pages\Page;

class DashboardSelector extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $title = 'Seleccionar Panel';

    protected static ?string $navigationLabel = 'Inicio';

    protected static ?int $navigationSort = -2; // Asegura que aparezca primero en la navegación

    protected static string $view = 'filament.pages.dashboard-selector';

    public function mount(): void
    {
        $panels = PanelRegistry::getAllPanels();
        if ($panels->count() === 1) {
            $this->redirect($panels->first()['url']);
        }
    }

    public function getHeading(): string
    {
        return 'Bienvenido al Sistema';
    }

    public function getSubheading(): string
    {
        return 'Seleccione el panel al que desea acceder';
    }

    protected function getPanels(): array
    {
        return PanelRegistry::getAllPanels()
            ->sortBy('sortOrder')
            ->toArray();
    }
}
