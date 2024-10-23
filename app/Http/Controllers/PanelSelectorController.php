<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

class PanelSelectorController extends Controller
{
    public function index()
    {
        // Obtenemos los paneles disponibles para el usuario actual
        $availablePanels = $this->getAvailablePanels();

        return view('panel-selector', [
            'panels' => $availablePanels
        ]);
    }


    private function getAvailablePanels()
    {
        $user = Auth::user();
        $user = auth()->user();
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
            ]
        ]);

         // filtrar según permisos del usuario
        return $panels->filter(function ($panel) use ($user) {
            return $user->hasPermissionToAccessPanel($panel['id']);
        });
    }
}
