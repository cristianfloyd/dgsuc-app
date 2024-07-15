<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ShowCuilDetails extends Component
{
    use WithPagination;

    public $cuilsNotInAfip = [];
    public $nroLiqui;
    public $periodoFiscal;
    public $perPage = 10;

    public function mount($nroLiqui, $periodoFiscal,array $cuilsNotInAfip)
    {
        $this->cuilsNotInAfip = $cuilsNotInAfip;
        $this->nroLiqui = $nroLiqui;
        $this->periodoFiscal = $periodoFiscal;
    }

    public function getResultadosProperty()
    {
        $resultados = collect();

        foreach ($this->cuilsNotInAfip as $cuil) {
            $resultados = $resultados->concat(
                DB::connection('pgsql-mapuche')
                ->select('SELECT * FROM suc.get_mi_simplificacion(?, ?, ?)', [$this->nroLiqui, $this->periodoFiscal, $cuil])
            );
        }
        return $resultados;
    }

    public function paginateResultados()
    {
        $page = request()->get('page', 1);
        $perPage = $this->perPage;
        $items = $this->resultados;

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }


    public function render()
    {
        $paginatedResultados = $this->resultados;

        return view('livewire.show-cuil-details', [
            'resultados' => $this->paginateResultados(),
        ]);
    }
}
