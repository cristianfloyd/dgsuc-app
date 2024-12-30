<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Pagination\LengthAwarePaginator;

class ShowCuilDetailsBatch extends Component
{
    use WithPagination, MapucheConnectionTrait;

    public $cuilsNotInAfip;
    public $failedCuils = [];
    public $nroLiqui;
    public $periodoFiscal;
    public $allResults;
    public $isLoading = true;
    public $progress = 0;
    public $currentPage = 1;
    private const RESULTS_PER_PAGE = 10;
    private const BATCH_SIZE = 20;

    protected $queryString = ['currentPage' => ['except' => 1]];

    public function mount($nroLiqui, $periodoFiscal, $cuilsNotInAfip)
    {
        $this->cuilsNotInAfip = $cuilsNotInAfip;
        $this->nroLiqui = $nroLiqui;
        $this->periodoFiscal = $periodoFiscal;
        $this->allResults = new Collection();
    }

    public function loadBatch()
{
    $totalCuils = count($this->cuilsNotInAfip);
    $processedCuils = $this->allResults->count() + count($this->failedCuils);
    $remainingCuils = array_slice($this->cuilsNotInAfip, $processedCuils, self::BATCH_SIZE);

    $query = 'SELECT * FROM suc.get_mi_simplificacion(?, ?, ?) WHERE cuil = ANY(?)';
    $params = [$this->nroLiqui, $this->periodoFiscal, implode(',', $remainingCuils)];

    try {
        $results = DB::connection($this->getConnectionName())->select($query, $params);

        foreach ($results as $result) {
            $this->allResults->push($result);
        }

        $processedCuils += count($results);
        $failedCuils = array_diff($remainingCuils, array_column($results, 'cuil'));
        $this->failedCuils = array_merge($this->failedCuils, $failedCuils);

        $this->progress = ($processedCuils / $totalCuils) * 100;
        $this->emit('progressUpdated', $this->progress);

        if ($processedCuils >= $totalCuils) {
            $this->isLoading = false;
        }

        $this->emit('batchLoaded');
    } catch (\Exception $e) {
        // Log the error
        Log::error('Error loading batch: ' . $e->getMessage());
        $this->failedCuils = array_merge($this->failedCuils, $remainingCuils);
        $this->isLoading = false;
        $this->emit('batchLoadingFailed');
    }
}

    public function getPaginatedResults()
    {
        return new LengthAwarePaginator(
            $this->allResults->forPage($this->currentPage, self::RESULTS_PER_PAGE),
            $this->allResults->count(),
            self::RESULTS_PER_PAGE,
            $this->currentPage,
            ['path' => '/show-cuil-details']
        );
    }

    public function setPage($page)
    {
        $this->currentPage = $page;
    }

    public function render()
    {
        return view('livewire.show-cuil-details-batch', [
            'paginatedResults' => $this->isLoading ? [] : $this->getPaginatedResults(),
            'failedCuils' => $this->failedCuils,
        ]);
    }
}
