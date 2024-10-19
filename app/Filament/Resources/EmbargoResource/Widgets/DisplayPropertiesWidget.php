<?php

namespace App\Filament\Resources\EmbargoResource\Widgets;

use View;
use Livewire\Livewire;
use App\Tables\EmbargoTable;
use Filament\Widgets\Widget;
use Filament\Resources\Resource;
use function PHPUnit\Framework\isNull;
use Filament\Widgets\WidgetConfiguration;

use App\Filament\Resources\EmbargoResource;
use App\Livewire\DynamicPropertiesComponent;

class DisplayPropertiesWidget extends Widget
{
    protected static string $view = 'filament.widgets.display-properties-widget';

    protected array $properties =  [];
    protected int $nroLiquiDefinitiva;

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


}
