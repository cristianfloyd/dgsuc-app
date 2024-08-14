<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class TestCuils extends Component
{
    public $periodoFiscal = 202312;
    public $nroLiqui = 1;
    public $cuil = 20190814670;
    public function render()
    {
        $data = DB::connection('pgsql-mapuche')
            ->select('select * from suc.get_mi_simplificacion(?, ?, ?)', [$this->nroLiqui, $this->periodoFiscal, $this->cuil]);
        return view('livewire.test-cuils');
    }
}
