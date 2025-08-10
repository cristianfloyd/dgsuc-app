<?php

namespace App\Livewire;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PanelSwitcherModal extends Component
{
    use InteractsWithActions;
    use InteractsWithForms;

    // Propiedad para controlar la visibilidad del modal
    public bool $isOpen = false;

    public function render()
    {
        return view('livewire.panel-switcher-modal', [
            'panels' => $this->getAvailablePanels(),
        ]);
    }

    // Método para cambiar de panel
    public function switchPanel(string $panelId): void
    {
        try {
            redirect()->to($this->getAvailablePanels()->firstWhere('id', $panelId)['url']);
        } catch (Halt $exception) {
            return;
        }
    }

    // Método para obtener los paneles disponibles
    private function getAvailablePanels(): \Illuminate\Support\Collection
    {
        return collect([
            [
                'id' => 'admin',
                'name' => 'Panel Administrativo',
                'url' => '/admin',
                'icon' => 'heroicon-o-cog',
                'color' => 'primary',
            ],
            [
                'id' => 'mapuche',
                'name' => 'Panel Mapuche',
                'url' => '/mapuche',
                'icon' => 'heroicon-o-document-text',
                'color' => 'success',
            ],
        ]);//->filter(fn ($panel) => Auth::user()->hasPermissionToAccessPanel($panel['id']))
    }
}
