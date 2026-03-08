<?php

namespace App\Filament\Afip\Widgets;

use App\Enums\ConceptosSicossEnum;
use Filament\Widgets\Widget;

class ConceptosSeleccionadosWidget extends Widget
{
    public array $conceptosSeleccionados = [];

    protected string $view = 'filament.afip.widgets.conceptos-seleccionados-widget';

    public function mount(): void
    {
        if (empty($this->conceptosSeleccionados)) {
            $this->conceptosSeleccionados = array_merge(
                ConceptosSicossEnum::getAllAportesCodes(),
                ConceptosSicossEnum::getAllContribucionesCodes(),
                ConceptosSicossEnum::getContribucionesArtCodes(),
            );
        }
    }
}
