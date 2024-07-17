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

    public $cuilsNotInAfip;
    public $failedCuils = [];
    public $nroLiqui;
    public $periodoFiscal;
    private const MAX_CUILS_PER_QUERY = 8;
    private const RESULTS_PER_PAGE = 4;
    private $resultadosCache = null;

    public function mount($nroLiqui, $periodoFiscal, $cuilsNotInAfip)
    {
        $this->cuilsNotInAfip = $cuilsNotInAfip;
        $this->nroLiqui = $nroLiqui;
        $this->periodoFiscal = $periodoFiscal;
    }

    public function getResultadosProperty()
    {
        if(!$this->resultadosCache){
            $limitedCuils = $this->getLimitedCuils();
            $this->resultadosCache = $this->getResultsForCuils($limitedCuils);
        }

        //paginar los resultados
        $paginator = new LengthAwarePaginator(
            collect($this->resultadosCache),
            count($this->resultadosCache),
            self::RESULTS_PER_PAGE,
            null,
            ['path'=>request()->url()]
        );
        // dd($this->resultadosCache,$paginator);
        return $paginator;
    }
    public function getResultsForCuils(array $cuils)
    {
        try {
            $results = [];

            foreach ($cuils as $cuil) {
                $query = 'SELECT * FROM suc.get_mi_simplificacion(?, ?, ?)';
                $params = [$this->nroLiqui, $this->periodoFiscal, $cuil];
                $result = DB::connection('pgsql-mapuche')->select($query, $params);

                if(empty($result)){
                    $failedCuils[] = $cuil;
                } else {
                    $results[] = $result;

                }
            }
        // You can now use the $failedCuils array to store or log the CUILs that didn't return any results
        // For example, you could add a property to your component to store the failed CUILs
        $this->failedCuils = $failedCuils;

        return $results;

    } catch (Exception $e) {
            // Handle exception
        }
    }
    public function getLimitedCuils()
    {
        return array_slice($this->cuilsNotInAfip,0, self::MAX_CUILS_PER_QUERY);
    }



    public function render()
    {
        return view('livewire.show-cuil-details');
    }
}

