<?php

namespace App\Livewire;

use App\Models\Dh01;
use App\Models\Dh03;
use Livewire\Component;
use App\Models\AfipMapucheSicoss;
use App\Models\AfipRelacionesActivas;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class CompareCuils extends Component
{
    use WithPagination;
    public $cuilsNotInAfip = [];
    public $cuilsNotInAfipLoaded = false;
    public $arrayDnis = [];
    public $selectedDni;
    public $employeeInfo;
    public $showModal = false;
    public $showCargoModal = false;
    public $cargos = [];
    public $load = false;
    public $perPage = 10;

    public function loadCuilsNotInAfip()
    {
        $this->cuilsNotInAfipLoaded = true;
        $this->compareCuils();
    }

    /**
     * Compara las CUIL (Clave Única de Identificación Laboral) del modelo AfipMapucheSicoss con las CUIL del modelo AfipRelacionesActivas.
     *
     * Este método recupera todos los CUIL del modelo AfipRelacionesActivas, y luego encuentra todos los CUIL del modelo AfipMapucheSicoss
     * que no están presentes en el modelo AfipRelacionesActivas.
     * Los CUIL resultantes que no están en el modelo AfipRelacionesActivas se almacenan en la propiedad $cuilsNotInAfip.
     * Además, el método crea una matriz de DNI (Documento Nacional de Identidad) eliminando los dos primeros caracteres
     * y el último carácter de cada CUIL en la matriz $cuilsNotInAfip, y lo almacena en la propiedad $arrayDnis.
     */
    #[Computed()]
    public function compareCuils() // Este metodo obtiene un array que ocupa 80mb de memoria.
    {
        $afipCuils = AfipRelacionesActivas::pluck('cuil')->toArray();

        $this->cuilsNotInAfip = AfipMapucheSicoss::whereNotIn('cuil', $afipCuils)
            ->pluck('cuil');
        $perPage = $this->perPage;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $this->cuilsNotInAfip
            ->slice(($currentPage - 1) * $perPage, $perPage)->all();

        return new LengthAwarePaginator(
            $currentPageItems,
            // count($this->cuilsNotInAfip),
            $this->cuilsNotInAfip->count(),
            $perPage
        );
    }




    public function mount()
    {
        // $this->compareCuils();
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
            ->get(['nro_cargo', 'codc_categ', 'fec_alta', 'fec_baja', 'vig_caano', 'vig_cames', 'chkstopliq'])
            ->toArray();

        $this->showCargoModal();
        $this->closeShowModal();
    }

    protected function showCargoModal()
    {
        $this->showCargoModal = true;
    }
    protected function closeShowModal()
    {
        $this->showModal = false;
    }

    public function closeCargoModal()
    {
        $this->showCargoModal = false;
        $this->cargos = [];
    }

    public function render()
    {
        return view('livewire.compare-cuils',[
            'cuilsPaginados' => $this->cuilsNotInAfip,
        ]);
    }
}
