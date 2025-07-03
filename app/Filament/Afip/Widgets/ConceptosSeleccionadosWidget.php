<?php

namespace App\Filament\Afip\Widgets;

use Filament\Widgets\Widget;

class ConceptosSeleccionadosWidget extends Widget
{
    protected static string $view = 'filament.afip.widgets.conceptos-seleccionados-widget';

    public array $conceptos = [];

    public function mount(array $conceptos = [])
    {
        $this->conceptos = $conceptos;
    }
}
