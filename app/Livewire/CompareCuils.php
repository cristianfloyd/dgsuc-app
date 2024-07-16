<?php

namespace App\Livewire;

use App\Models\Dh01;
use App\Models\Dh03;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AfipMapucheSicoss;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use App\Models\AfipRelacionesActivas;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;

class CompareCuils extends Component
{
    use WithPagination;
    public $cuilsNotInAfip = [];
    public $nroLiqui = 1;
    public $periodoFiscal = '202312';
    public $cuilstosearch = [];
    public $cuilsNotInAfipLoaded = false;
    public $arrayDnis = [];
    public $selectedDni;
    public $employeeInfo;
    public $showModal = false;
    public $showCargoModal = false;
    public $cargos = [];
    public $load = false;
    public $perPage = 10;
    public $showDetails = false;



    public function showCuilsDetails()
    {
        $this->toggleCuils($this->cuilsNotInAfipLoaded);
        $this->toggleShow($this->showDetails);
        // dump('Hola Mundo!');
        // $this->cuilstosearch = $this->cuilsNotInAfip->toArray();
    }

    public function hideCuilDetails()
    {
        $this->showDetails = false;
    }

    public function toggleShow($value): void
    {
        $this->showDetails = (bool) $value === false;
    }
    public function toggleCuils($value): void
    {
        $this->cuilsNotInAfipLoaded = (bool) $value === false;
    }
    public function loadCuilsNotInAfip()
    {
        $this->toggleCuils($this->cuilsNotInAfipLoaded);
        $this->compareCuils();
    }

    /**
     * Compara las CUIL (Clave Única de Identificación Laboral) del modelo AfipMapucheSicoss con las CUIL del modelo AfipRelacionesActivas.
     *
     * Este método recupera todos los CUIL del modelo AfipRelacionesActivas, y luego encuentra todos los CUIL del modelo AfipMapucheSicoss
     * que no están presentes en el modelo AfipRelacionesActivas.
     * Los CUIL resultantes que no están en el modelo AfipRelacionesActivas se almacenan en la propiedad $cuilsNotInAfip.
     */
    #[Computed()]
    public function compareCuils() // ya fue optimizado v1.0
    {
        $afipCuils = AfipRelacionesActivas::pluck('cuil')->toArray();

        $this->cuilsNotInAfip = AfipMapucheSicoss::whereNotIn('cuil', $afipCuils)
            ->pluck('cuil');
        $this->cuilstosearch = $this->cuilsNotInAfip;
        $this->cuilstosearch = $this->cuilstosearch->toArray();
        // dd($this->cuilstosearch);
        // dd($this->cuilsNotInAfip);

        $perPage = $this->perPage;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $this->cuilsNotInAfip
            ->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $data =  new LengthAwarePaginator(  //retorna los cuils paginados en una coleccion
            $currentPageItems,
            // count($this->cuilsNotInAfip),
            $this->cuilsNotInAfip->count(),
            $perPage
        );
        return $data;
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
        return view('livewire.compare-cuils');
    }
}
