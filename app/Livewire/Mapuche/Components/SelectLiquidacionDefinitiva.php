<?php

namespace App\Livewire\Mapuche\Components;

use App\Services\Mapuche\Dh22Service;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Application;
use Livewire\Component;

class SelectLiquidacionDefinitiva extends Component
{
    public Collection | array $liquidaciones = [];
    public ?int $liquidacionSeleccionada = null;
    public ?int $year = null;
    public ?int $month = null;

    protected $listeners = ['updateLiquidaciones'];

    public function mount(Dh22Service $dh22Service, int $year = null, int $month = null): void
    {
        $this->updateLiquidaciones($dh22Service);
        $this->year = $year;
        $this->month = $month;
    }

    /**
     * Actualiza la lista de liquidaciones definitivas obtenidas desde el servicio Dh22Service.
     *
     * @param Dh22Service $dh22Service El servicio que proporciona las liquidaciones definitivas.
     * @return void
     */
    public function updateLiquidaciones(Dh22Service $dh22Service): void
    {
        $this->liquidaciones = $dh22Service->getLiquidacionesDefinitivas($this->year, $this->month);
    }

    public function updatedLiquidacionSeleccionada(): void
    {
        $this->dispatch('liquidacionSeleccionada', $this->liquidacionSeleccionada);
    }

    public function render(): View|Factory|Application
    {
        return view('livewire.Mapuche.Components.select-liquidacion-definitiva');
    }
}
