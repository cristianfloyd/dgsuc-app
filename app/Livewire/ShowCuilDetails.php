<?php

namespace App\Livewire;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ShowCuilDetails extends Component
{
    use WithPagination;
    use MapucheConnectionTrait;

    private const RESULTS_PER_PAGE = 10;

    public $cuilsNotInAfip;

    public $failedCuils = [];

    public $nroLiqui;

    public $periodoFiscal;

    public $allResults;

    protected $queryString = ['page' => ['except' => 1]];

    public function mount($nroLiqui, $periodoFiscal, $cuilsNotInAfip): void
    {
        $this->cuilsNotInAfip = $cuilsNotInAfip;
        $this->nroLiqui = $nroLiqui;
        $this->periodoFiscal = $periodoFiscal;
        $this->allResults = $this->getAllResults();
    }

    public function getPaginatedResultsProperty()
    {
        $page = $this->page;
        return new LengthAwarePaginator(
            $this->allResults->forPage($page, self::RESULTS_PER_PAGE),
            $this->allResults->count(),
            self::RESULTS_PER_PAGE,
            $page,
            ['path' => '/show-cuil-details'],
        );
    }

    public function render()
    {
        return view('livewire.show-cuil-details', [
            'paginatedResults' => $this->paginatedResults,
            'failedCuils' => $this->failedCuils,
        ]);
    }

    private function getAllResults()
    {
        $results = new Collection();
        foreach ($this->cuilsNotInAfip as $cuil) {
            try {
                $query = 'SELECT * FROM suc.get_mi_simplificacion(?, ?, ?)';
                $params = [$this->nroLiqui, $this->periodoFiscal, $cuil];
                $result = DB::connection($this->getConnectionName())->select($query, $params);

                if (empty($result)) {
                    $this->failedCuils[] = $cuil;
                } else {
                    $results->push($result[0]);
                }
            } catch (\Exception $e) {
                $this->failedCuils[] = $cuil;
                // Log the error if needed
            }
        }
        return $results;
    }
}
