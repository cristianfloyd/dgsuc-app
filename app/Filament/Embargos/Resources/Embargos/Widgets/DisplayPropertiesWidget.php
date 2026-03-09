<?php

namespace App\Filament\Embargos\Resources\Embargos\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class DisplayPropertiesWidget extends Widget
{
    protected string $view = 'filament.widgets.display-properties-widget';

    protected array $properties = [];

    public function mount($properties): void
    {
        $this->properties = $properties;
    }

    public function render(): View
    {

        // Montar el componente Livewire y pasar las propiedades iniciales
        return view(static::$view, [
            'initialProperties' => $this->properties,
        ]);
    }

    #[On('propertiesUpdated')]
    public function updatedProperties(array $properties): void
    {
        $this->properties = $properties;
        Log::info('evento escuchado en el widget', $this->properties);
    }

    protected function getInitialProperties(): array
    {
        // Este método puede ser sobrescrito por cada recurso para proporcionar las propiedades iniciales
        return [];
    }
}
