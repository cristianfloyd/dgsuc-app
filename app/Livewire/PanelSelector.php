<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class PanelSelector extends Component
{
    public function render()
    {
        return view('livewire.panel-selector', [
            'panels' => $this->getAvailablePanels()
        ]);
    }

    private function getAvailablePanels()
    {
        return collect([
            [
                'id' => 'admin',
                'name' => 'Panel Administrativo',
                'url' => '/admin',
                'icon' => 'heroicon-o-cog',
            ],
            [
                'id' => 'mapuche',
                'name' => 'Panel Mapuche',
                'url' => '/mapuche',
                'icon' => 'heroicon-o-document-text',
            ],
        ])//->filter(fn ($panel) => Auth::user()->hasPermissionToAccessPanel($panel['id']))
        ;
    }
}
