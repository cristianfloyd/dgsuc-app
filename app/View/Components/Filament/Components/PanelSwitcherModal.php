<?php

namespace App\View\Filament\Components;

use Illuminate\Contracts\View\View;
use Filament\Support\Components\ViewComponent;
use Filament\Support\Concerns\HasExtraAttributes;

class PanelSwitcherModal extends ViewComponent
{
    // Traits necesarios
    use HasExtraAttributes;

    // Propiedad para controlar visibilidad del modal
    public bool $isOpen = false;

    // Método para obtener los paneles disponibles
    protected function getPanels(): array
    {
        return collect([
            [
                'id' => 'admin',
                'name' => 'Panel Administrativo',
                'icon' => 'heroicon-o-cog',
                'url' => '/admin',
                'color' => 'primary',
            ],
            [
                'id' => 'mapuche',
                'name' => 'Panel Mapuche',
                'icon' => 'heroicon-o-document-text',
                'url' => '/mapuche',
                'color' => 'success',
            ],
        ])//->filter(fn ($panel) => Auth::user()->hasPermissionToAccessPanel($panel['id']))
        ->toArray();
    }

    public function render(): View
    {
        return view('filament.components.panel-switcher-modal', [
            'panels' => $this->getPanels(),
        ]);
    }
}
