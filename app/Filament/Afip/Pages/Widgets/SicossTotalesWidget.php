<?php

namespace App\Filament\Afip\Pages\Widgets;

use Livewire\Attributes\On;
use Filament\Widgets\Widget;
use Filament\Support\Colors\Color;

class SicossTotalesWidget extends Widget
{
    protected static string $view = 'filament.afip.pages.widgets.sicoss-totales-widget';

    /**
     * @var array<string, float>
     */
    public array $totales = [];

    public bool $isCollapsed = true;

    protected int | string | array $columnSpan = 'full';

    /**
     * Determina si el widget debe ser renderizado.
     *
     * Se ha mejorado la detección para considerar el caso en que los totales se hayan obtenido,
     * incluso si todos son 0, o para permitir ver información de depuración.
     *
     * @return bool
     */
    public function shouldLoad(): bool
    {
        try {
            return !empty($this->totales);
        } catch (\Exception $e) {
            // En caso de error, registramos la excepción y evitamos bloquear la vista del widget.
            logger()->error('Error en shouldLoad de SicossTotalesWidget: ' . $e->getMessage());
            return false;
        }
    }

    #[On('totales-actualizados')]
    public function updateTotales(array $totales): void
    {
        $this->totales = $totales;
    }

    public function toggleCollapsed(): void
    {
        $this->isCollapsed = !$this->isCollapsed;
    }

    public function setTotales(array $totales): static
    {
        $this->totales = $totales;
        return $this;
    }

    protected function getPreloadedContent(): ?string
    {
        return null; // Evita la precarga innecesaria
    }
}
