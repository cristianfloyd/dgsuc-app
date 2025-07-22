<?php

namespace App\Filament\Resources\Dh21Resource\Widgets;

use App\Services\Dh21Service;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;

class Dh21Concepto101Total extends Widget
{
    public $nroLiqui = 1;

    public $totalConcepto101 = 0;

    protected static string $vista = 'filament.widgets.dh21-concepto101-total';

    protected $dh21;

    public function boot(Dh21Service $dh21): void
    {
        $this->dh21 = $dh21;
    }

    public function mount(): void
    {
        $this->updateTotal();
    }

    #[On('idLiquiSelected')]
    public function updateTotal($nroLiqui = null): void
    {
        $this->nroLiqui = $nroLiqui;
        $this->totalConcepto101 = $this->dh21->totalConcepto101($nroLiqui);
    }

    public function render(): View
    {
        return view(static::$vista, [
            'total' => $this->totalConcepto101,
            'nroLiqui' => $this->nroLiqui,
        ]);
    }
}
