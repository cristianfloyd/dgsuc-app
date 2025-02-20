<?php

namespace App\Filament\Afip\Pages\Widgets;

use Filament\Widgets\Widget;
use Filament\Support\Colors\Color;

class SicossTotalesWidget extends Widget
{
    protected static string $view = 'filament.afip.pages.widgets.sicoss-totales-widget';

    /**
     * @var array<string, float>
     */
    public array $totales = [];

    protected int | string | array $columnSpan = 'full';

    /**
     * Determina si el widget debe ser renderizado.
     *
     * @return bool
     */
    public function shouldLoad(): bool
    {
        return !empty($this->totales) &&
            array_sum(array_map(fn($value) => (float)$value, $this->totales)) > 0;
    }
}
