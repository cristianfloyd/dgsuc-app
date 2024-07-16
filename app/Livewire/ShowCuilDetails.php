<?php

namespace App\Livewire;

use Exception;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ShowCuilDetails extends Component
{
    use WithPagination;

    public $cuilsNotInAfip = null;
    public $nroLiqui;
    public $periodoFiscal;
    public $perPage = 10;

    public function mount($nroLiqui, $periodoFiscal, $cuilsNotInAfip)
    {
        $this->cuilsNotInAfip = $cuilsNotInAfip;
        $this->nroLiqui = $nroLiqui;
        $this->periodoFiscal = $periodoFiscal;
    }

    public function getResultadosProperty()
    {
        return $this->processCuilsNotInAfip();
    }

    public function processCuilsNotInAfip()
    {
        $resultados = collect();

        $cuilsNotInAfipLimited = $this->limitCuilsTo10($this->cuilsNotInAfip);

        foreach ($cuilsNotInAfipLimited as $cuil) {
            $resultados = $resultados->concat(
                DB::connection('pgsql-mapuche')
                ->select('SELECT * FROM suc.get_mi_simplificacion(?, ?, ?) limit 10', [$this->nroLiqui, $this->periodoFiscal, $cuil])
            );
        }


        return $resultados;
    }

    private function limitCuilsTo10($cuils)
    {
        $cuilsLimited = array_slice($cuils, 0, 10);

        // Corroborar que solo queden 10 elementos en el array
        if ($cuilsLimited > 10) {
            // throw new Exception('Error: más de 10 elementos en el array');
            // dump('$ciulsLimited');
        }

        return $cuilsLimited;
    }
    // #[Computed()]
    // public function resultados()
    // {
    //     $page = request()->get('page', 1);
    //     $perPage = $this->perPage;
    //     $items = $this->resultados;

    //     return new LengthAwarePaginator(
    //         $items->forPage($page, $perPage),
    //         $items->count(),
    //         $perPage,
    //         $page,
    //         ['path' => request()->url(), 'query' => request()->query()]
    //     );
    // }


    public function render()
    {
        return view('livewire.show-cuil-details');
    }
}
