<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;

class PanelSelectorController extends Controller
{
    public function index(): View|Factory|Application
    {
        // Obtenemos los paneles disponibles para el usuario actual
        $availablePanels = $this->getAvailablePanels();

        return view('panel-selector', [
            'panels' => $availablePanels
        ]);
    }


    private function getAvailablePanels(): Collection
    {
        $panels = collect([
            [
                'id' => 'admin',
                'name' => 'Panel Administrativo',
                'icon' => 'heroicon-o-cog',
                'url' => '/dashboard',
                'description' => 'Gestión administrativa del sistema'
            ],
            [
                'id' => 'mapuche',
                'name' => 'Panel de mapuche',
                'icon' => 'heroicon-o-document-text',
                'url' => '/mapuche',
                'description' => 'Herramientas para el manejo de datos en mapuche'
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
        ]);

         // filtrar según permisos del usuario
        return $panels->filter(function () {
            // $data = $user->hasPermissionToAccessPanel($panel['id']);
            return  true;
        });
    }
}
