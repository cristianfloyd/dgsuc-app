<?php

namespace App\Livewire;

use App\Models\Dh01;
use App\Models\Dh03;
use Livewire\Component;
use App\Models\AfipMapucheSicoss;
use App\Models\AfipRelacionesActivas;



class CompareCuils extends Component
{
    public $cuilsNotInAfip = [];
    public $arrayDnis = [];
    public $selectedDni;
    public $employeeInfo;
    public $showModal = false;
    public $showCargoModal = false;
    public $cargos = [];


    public function compareCuils()
    {
        $afipCuils = AfipRelacionesActivas::pluck('cuil')->toArray();

        $this->cuilsNotInAfip = AfipMapucheSicoss::whereNotIn('cuil', $afipCuils)
            ->pluck('cuil')
            ->toArray();

        // Crear arrayDni quitando los dos primeros caracteres y el último de cada cuil
        $this->arrayDnis = array_map(function ($cuil) {
            return substr($cuil, 2, -1);
        }, $this->cuilsNotInAfip);
    }

    public function mount()
    {
        $this->compareCuils();
    }

    public function searchEmployee($dni)
    {
        $this->selectedDni = $dni;
        $employee = Dh01::where('nro_docum', $dni)->first();

        if ($employee) {
            $this->employeeInfo = [
                'nombre' => $employee->desc_nombr,
                'apellido' => $employee->desc_appat . ' ' . $employee->desc_apmat,
                'nro_legaj' => $employee->nro_legaj,
                'DNI' => $employee->nro_docum,
                'fecha_inicio' => $employee->dh03()->orderBy('fec_alta', 'asc')->value('fec_alta'),
            ];
            $this->showModal = true;
        } else {
            $this->employeeInfo = null;
            $this->showModal = true;
        }
    }
    public function closeModal()
    {
        $this->showModal = false;
        $this->employeeInfo = null;
        $this->selectedDni = null;
    }

    public function showCargos($nroLegaj)
    {
        $this->cargos = Dh03::where('nro_legaj', $nroLegaj)
            ->orderBy('fec_alta', 'desc')
            ->get(['nro_cargo','codc_categ','fec_alta', 'fec_baja','vig_caano', 'vig_cames','chkstopliq'])
            ->toArray();

        $this->showCargoModal = true;
        $this->showModal = false;
    }

    public function closeCargoModal()
    {
        $this->showCargoModal = false;
        $this->cargos = [];
    }

    public function render()
    {
        return view('livewire.compare-cuils');
    }
}
