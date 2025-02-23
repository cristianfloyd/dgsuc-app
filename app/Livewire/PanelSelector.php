<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;

class PanelSelector extends Component
{
    public string $search = '';
    public string $selectedCategory = 'all';
    public bool $darkMode = true;

    public function mount()
    {
        $this->darkMode = session('darkMode', true);
    }

    public function toggleTheme()
    {
        $this->darkMode = !$this->darkMode;
        session(['darkMode' => $this->darkMode]);
        $this->dispatch('toggle-theme');
    }

    public function render(): View
    {
        return view('livewire.panel-selector', [
            'panels' => $this->getFilteredPanels(),
            'categories' => $this->getCategories()
        ]);
    }

    private function getFilteredPanels(): Collection
    {
        return $this->getAvailablePanels()
            ->when($this->search, fn($panels) =>
                $panels->filter(fn($panel) =>
                    str_contains(strtolower($panel['name']), strtolower($this->search)) ||
                    str_contains(strtolower($panel['description'] ?? ''), strtolower($this->search))
                )
            )
            ->when($this->selectedCategory !== 'all', fn($panels) =>
                $panels->where('category', $this->selectedCategory)
            );
    }

    private function getCategories(): Collection
    {
        return collect([
            'all' => 'Todos los Paneles',
            'admin' => 'Administración',
            'rrhh' => 'Recursos Humanos',
            'finance' => 'Finanzas',
            'reports' => 'Reportes'
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
                'category' => 'admin',
                'description' => 'Gestión administrativa del sistema',
                'bgColor' => 'bg-slate-700'
            ],
            [
                'id' => 'dashboard',
                'name' => 'Escritorio SUC',
                'icon' => 'heroicon-o-clipboard-document-list',
                'url' => '/dashboard',
                'description' => 'S.U.C. - Panel de control de operaciones',
                'category' => 'admin',
                'bgColor' => 'bg-slate-600'
            ],
            [
                'id' => 'mapuche',
                'name' => 'Panel Mapuche',
                'url' => '/mapuche',
                'icon' => 'heroicon-o-document-text',
                'category' => 'rrhh',
                'description' => 'Gestión de recursos humanos y personal',
                'bgColor' => 'bg-slate-800'
            ],
            [
                'id' => 'embargos',
                'name' => 'Panel Embargos',
                'icon' => 'heroicon-o-document-duplicate',
                'url' => '/embargos',
                'description' => 'Gestión de embargos',
                'category' => 'finance',
                'bgColor' => 'bg-slate-700'
            ],
            [
                'id' => 'sicoss',
                'name' => 'Panel SICOSS',
                'icon' => 'heroicon-o-document-duplicate',
                'url' => '/sicoss',
                'description' => 'Gestión de importación y procesamiento SICOSS',
                'category' => 'rrhh',
                'bgColor' => 'bg-slate-600'
            ],
            [
                'id' => 'afip',
                'name' => 'Panel AFIP',
                'icon' => 'heroicon-o-clipboard-document-list',
                'url' => '/afip-panel',
                'description' => 'Gestión de datos y relaciones con AFIP',
                'category' => 'finance',
                'bgColor' => 'bg-slate-800'
            ],
            [
                'id' => 'reportes',
                'name' => 'Panel de Reportes',
                'icon' => 'heroicon-o-chart-bar',
                'url' => '/reportes',
                'description' => 'Generación y visualización de reportes',
                'category' => 'reports',
                'bgColor' => 'bg-slate-700'
            ],
            [
                'id' => 'liquidaciones',
                'name' => 'Panel de Liquidaciones',
                'icon' => 'heroicon-o-currency-dollar',
                'url' => '/liquidaciones',
                'description' => 'Gestión de liquidaciones y órdenes de pago',
                'category' => 'finance',
                'bgColor' => 'bg-slate-600'
            ]
        ]);
    }
}
