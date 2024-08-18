<?php

namespace App\Filament\Resources\Dh21Resource\Widgets;

use App\Models\Dh21;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;

class Dh21Concepto101Total extends Widget
{
    protected static string $vista = 'filament.widgets.dh21-concepto101-total';

    public $nroLiqui = 1;
    public $totalConcepto101 = 0;


    public function mount()
    {
        $this->updateTotal();
    }

    #[On('idLiquiSelected')]
    public function updateTotal($nroLiqui = null)
    {
        $this->nroLiqui = $nroLiqui;
        $this->totalConcepto101 = Dh21::totalConcepto101($nroLiqui);
    }

    public function render(): View
    {
        return view(static::$vista, [
            'total' => $this->totalConcepto101,
            'nroLiqui' => $this->nroLiqui,
        ]);
    }
}
