<?php

namespace App\Livewire;

use App\Models\AfipMapucheMiSimplificacion as ModelsAfipMapucheMiSimplificacion;
use App\Models\dh21;
use App\Models\dh03;
use App\Models\dh01;
use App\Models\dh22;
use App\Models\EstadoLiquidacion;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class AfipMapucheMiSimplificacion extends Component
{

    use WithPagination;
    public $search = '';
    public $porPagina = 10;
    public $resultados;


    public function mount()
    {
        $this->afipMapucheMiSimplificacion();
    }
    public function afipMapucheMiSimplificacion()
    {
        //obtengo los datos de la tabla afip_mapuche_mi_simplificacion paginados de a 10
        //$this->datos = ModelsAfipMapucheMiSimplificacion::paginate(10);
    }



    public function render()
    {

        return view('livewire.afip-mapuche-mi-simplificacion',
        [
            'datos' => ModelsAfipMapucheMiSimplificacion::paginate($this->porPagina),
        ]);
    }
}
