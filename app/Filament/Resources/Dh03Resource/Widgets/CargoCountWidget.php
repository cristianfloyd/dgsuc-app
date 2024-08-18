<?php

namespace App\Filament\Resources\Dh03Resource\Widgets;

use App\Models\Dh03;
use Filament\Widgets\Widget;

class CargoCountWidget extends Widget
{
    protected static string $name = 'Cargo Count';
    protected static string $view = 'filament.widgets.cargo-count-widget';

    protected function getData(): array
    {
        return [
            'cargoCount' => Dh03::count(),
        ];
    }
}
