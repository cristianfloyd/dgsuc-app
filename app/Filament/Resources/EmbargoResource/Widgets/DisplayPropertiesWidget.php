<?php

namespace App\Filament\Resources\EmbargoResource\Widgets;

use Livewire\Attributes\On;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Log;



class DisplayPropertiesWidget extends Widget
{
    protected static string $view = 'filament.widgets.display-properties-widget';

    protected array $properties =  [];


    public function mount($properties): void
    {
        $this->properties = $properties;
    }

    public function render(): \Illuminate\Contracts\View\View
    {

        // Montar el componente Livewire y pasar las propiedades iniciales
        return view(static::$view, [
            'initialProperties' => $this->properties,
        ]);
    }



    protected function getInitialProperties(): array
    {
        // Este método puede ser sobrescrito por cada recurso para proporcionar las propiedades iniciales
        return [];
    }

    #[On('propertiesUpdated')]
    public function updatedProperties(array $properties): void
    {
        $this->properties = $properties;
        Log::info('evento escuchado en el widget', $this->properties);
    }
}
