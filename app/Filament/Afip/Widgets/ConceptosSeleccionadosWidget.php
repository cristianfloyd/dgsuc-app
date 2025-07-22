<?php

namespace App\Filament\Afip\Widgets;

use Filament\Widgets\Widget;

class ConceptosSeleccionadosWidget extends Widget
{
    public array $conceptosSeleccionados = [];

    protected static string $view = 'filament.afip.widgets.conceptos-seleccionados-widget';

    public function mount(): void
    {
        if (empty($this->conceptosSeleccionados)) {
            $this->conceptosSeleccionados = array_merge(
                \App\Enums\ConceptosSicossEnum::getAllAportesCodes(),
                \App\Enums\ConceptosSicossEnum::getAllContribucionesCodes(),
                \App\Enums\ConceptosSicossEnum::getContribucionesArtCodes(),
            );
        }
    }
}
