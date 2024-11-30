<?php

namespace App\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Livewire\Component;

class PanelSelector extends Component
{
    public function render(): View|Factory|Application
    {
        return view('livewire.panel-selector', [
            'panels' => $this->getAvailablePanels()
        ]);
    }

    private function getAvailablePanels(): Collection
    {
        return collect([
            [
                'id' => 'admin',
                'name' => 'Panel Administrativo',
                'url' => '/admin',
                'icon' => 'heroicon-o-cog',
            ],
            [
                'id' => 'dashboard',
                'name' => 'Escritorio SUC',
                'icon' => 'heroicon-o-clipboard-document-list',
                'url' => '/dashboard',
                'description' => 'S.U.C. - Panel de control de operaciones'
            ],
            [
                'id' => 'mapuche',
                'name' => 'Panel Mapuche',
                'url' => '/mapuche',
                'icon' => 'heroicon-o-document-text',
            ],
            [
                'id' => 'embargos',
                'name' => 'Panel Embargos',
                'icon' => 'heroicon-o-document-duplicate',
                'url' => '/embargos',
                'description' => 'Gestión de embargos'
            ],
            [
                'id' => 'sicoss',
                'name' => 'Panel SICOSS',
                'icon' => 'heroicon-o-document-duplicate',
                'url' => '/sicoss',
                'description' => 'Gestión de importación y procesamiento SICOSS'
            ],
            [
                'id' => 'afip',
                'name' => 'Panel AFIP',
                'icon' => 'heroicon-o-clipboard-document-list',
                'url' => '/afip',
                'description' => 'Gestión de datos y relaciones con AFIP'
            ],
            [
                'id' => 'reportes',
                'name' => 'Panel de Reportes',
                'icon' => 'heroicon-o-chart-bar',
                'url' => '/reportes',
                'description' => 'Generación y visualización de reportes'
            ],
            [
                'id' => 'liquidaciones',
                'name' => 'Panel de Liquidaciones',
                'icon' => 'heroicon-o-currency-dollar',
                'url' => '/liquidaciones',
                'description' => 'Gestión de liquidaciones y órdenes de pago'
            ]
        ])//->filter(fn ($panel) => Auth::user()->hasPermissionToAccessPanel($panel['id']))
        ;
    }
}
