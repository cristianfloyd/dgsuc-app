<?php

namespace App\Http\Livewire\Mapuche\Components;

use Livewire\Component;
use App\Services\Mapuche\Dh22Service;

class SelectLiquidacionDefinitiva extends Component
{
    public $liquidaciones = [];
    public $liquidacionSeleccionada = null;
    public $year = null;
    public $month = null;

    protected $listeners = ['updateLiquidaciones'];

    public function mount(Dh22Service $dh22Service, int $year = null, int $month = null)
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
    public function updateLiquidaciones(Dh22Service $dh22Service)
    {
        $this->liquidaciones = $dh22Service->getLiquidacionesDefinitivas($this->year, $this->month);
    }

    public function updatedLiquidacionSeleccionada()
    {
        $this->dispatch('liquidacionSeleccionada', $this->liquidacionSeleccionada);
    }

    public function render()
    {
        return view('livewire.Mapuche.Components.select-liquidacion-definitiva');
    }
}
