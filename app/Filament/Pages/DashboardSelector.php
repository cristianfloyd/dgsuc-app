<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Enums\IconPosition;

class DashboardSelector extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $title = 'Seleccionar Panel';
    protected static ?string $navigationLabel = 'Inicio';
    protected static ?int $navigationSort = -2; // Asegura que aparezca primero en la navegación

    protected static string $view = 'filament.pages.dashboard-selector';

    public function mount(): void
    {
        // Redirigir si solo hay un panel disponible
        $panels = $this->getPanels();
        if (count($panels) === 1) {
            $this->redirect($panels[0]['url']);
        }
    }

    protected function getPanels(): array
    {
        return [
            [
                'name' => 'Panel Administrativo',
                'icon' => 'heroicon-o-cog-6-tooth',
                'url' => '/admin',
                'description' => 'Gestión administrativa y configuración del sistema',
                'color' => 'primary',
                'badge' => 'Admin'
            ],
            [
                'name' => 'Panel AFIP',
                'icon' => 'heroicon-o-building-office',
                'url' => '/afip-panel',
                'description' => 'Gestión de trámites y servicios AFIP',
                'color' => 'emerald',
                'badge' => 'AFIP'
            ],
            [
                'name' => 'Panel de Embargos',
                'icon' => 'heroicon-o-scale',
                'url' => '/embargos',
                'description' => 'Gestión y seguimiento de embargos',
                'color' => 'amber',
                'badge' => 'Embargos'
            ],
            [
                'name' => 'Panel de Liquidaciones',
                'icon' => 'heroicon-o-calculator',
                'url' => '/liquidaciones',
                'description' => 'Sistema de liquidación de haberes',
                'color' => 'emerald',
                'badge' => 'Liquidaciones'
            ],
            [
                'name' => 'Panel de Reportes',
                'icon' => 'heroicon-o-document-chart-bar',
                'url' => '/reportes',
                'description' => 'Generación y visualización de informes',
                'color' => 'amber',
                'badge' => 'Reportes'
            ],
            [
                'name' => 'Panel SUC',
                'icon' => 'heroicon-o-home',
                'url' => '/dashboard',
                'description' => 'Sistema Único de Control',
                'color' => 'amber',
                'badge' => 'SUC'
            ],
            [
                'name' => 'Panel Mapuche',
                'icon' => 'heroicon-o-user-group',
                'url' => '/mapuche',
                'description' => 'Herramientas de gestión Mapuche',
                'color' => 'blue',
                'badge' => 'Mapuche'
            ],
        ];
    }

    public function getHeading(): string
    {
        return 'Bienvenido al Sistema';
    }

    public function getSubheading(): string
    {
        return 'Seleccione el panel al que desea acceder';
    }
}
