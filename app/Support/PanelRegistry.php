<?php

namespace App\Support;

use Illuminate\Support\Collection;

class PanelRegistry
{
    public static function getAllPanels(): Collection
    {
        return collect([
            [
                'id' => 'admin',
                'name' => 'Panel Administrativo',
                'url' => '/admin',
                'icon' => 'heroicon-o-cog-6-tooth',
                'category' => 'admin',
                'description' => 'Gestión administrativa del sistema',
                'color' => 'primary',
                'badge' => 'Admin',
                'bgColor' => 'bg-slate-700',
                'sortOrder' => 10,
            ],
            [
                'id' => 'afip-panel',
                'name' => 'Panel AFIP',
                'url' => '/afip-panel',
                'icon' => 'heroicon-o-building-office',
                'category' => 'finance',
                'description' => 'Gestión de trámites y servicios AFIP',
                'color' => 'emerald',
                'badge' => 'AFIP',
                'bgColor' => 'bg-slate-700',
                'sortOrder' => 20,
            ],
            [
                'id' => 'embargos',
                'name' => 'Panel de Embargos',
                'url' => '/embargos',
                'icon' => 'heroicon-o-scale',
                'category' => 'finance',
                'description' => 'Gestión y seguimiento de embargos',
                'color' => 'amber',
                'badge' => 'Embargos',
                'bgColor' => 'bg-slate-700',
                'sortOrder' => 30,
            ],
            [
                'id' => 'liquidaciones',
                'name' => 'Panel de Liquidaciones',
                'url' => '/liquidaciones',
                'icon' => 'heroicon-o-calculator',
                'category' => 'finance',
                'description' => 'Sistema de liquidación de haberes',
                'color' => 'emerald',
                'badge' => 'Liquidaciones',
                'bgColor' => 'bg-slate-700',
                'sortOrder' => 40,
            ],
            [
                'id' => 'reportes',
                'name' => 'Panel de Reportes',
                'url' => '/reportes',
                'icon' => 'heroicon-o-document-chart-bar',
                'category' => 'reports',
                'description' => 'Generación y visualización de informes',
                'color' => 'amber',
                'badge' => 'Reportes',
                'bgColor' => 'bg-slate-700',
                'sortOrder' => 50,
            ],
            [
                'id' => 'suc',
                'name' => 'Panel SUC',
                'url' => '/dashboard',
                'icon' => 'heroicon-o-home',
                'category' => 'admin',
                'description' => 'Sistema Único de Control',
                'color' => 'amber',
                'badge' => 'SUC',
                'bgColor' => 'bg-slate-700',
                'sortOrder' => 60,
            ],
            [
                'id' => 'mapuche',
                'name' => 'Panel Mapuche',
                'url' => '/mapuche',
                'icon' => 'heroicon-o-user-group',
                'category' => 'rrhh',
                'description' => 'Herramientas de gestión Mapuche',
                'color' => 'blue',
                'badge' => 'Mapuche',
                'bgColor' => 'bg-slate-700',
                'sortOrder' => 70,
            ],
        ]);
    }

    public static function getCategories(): Collection
    {
        return collect([
            'all' => 'Todos los Paneles',
            'admin' => 'Administración',
            'rrhh' => 'Recursos Humanos',
            'finance' => 'Finanzas',
            'reports' => 'Reportes'
        ]);
    }
}
