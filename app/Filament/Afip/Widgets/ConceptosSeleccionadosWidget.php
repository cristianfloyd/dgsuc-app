<?php

namespace App\Filament\Afip\Widgets;

use Filament\Widgets\Widget;

class ConceptosSeleccionadosWidget extends Widget
{
    protected static string $view = 'filament.afip.widgets.conceptos-seleccionados-widget';

    public array $conceptosSeleccionados = [];

    public function mount()
    {
        if (empty($this->conceptosSeleccionados)) {
            $this->conceptosSeleccionados = array_merge(
                \App\Enums\ConceptosSicossEnum::getAllAportesCodes(),
                \App\Enums\ConceptosSicossEnum::getAllContribucionesCodes(),
                \App\Enums\ConceptosSicossEnum::getContribucionesArtCodes()
            );
        }
    }
}
