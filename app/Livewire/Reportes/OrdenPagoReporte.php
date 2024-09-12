<?php

namespace App\Livewire\Reportes;

use App\Models\Reportes\OrdenPagoMapuche;
use App\Repositories\Interfaces\RepOrdenPagoRepositoryInterface;
use Livewire\Component;

class OrdenPagoReporte extends Component
{
    public $reportData;
    protected $repOrdenPagoRepository;

    public function __construct(RepOrdenPagoRepositoryInterface $repOrdenPagoRepository)
    {
        $this->repOrdenPagoRepository = $repOrdenPagoRepository;
    }

    public function boot()
    {

    }

    public function mount()
    {
        $this->loadReportData();
    }


    public function loadReportData()
    {
        $this->reportData = $this->convertToBaseCollection(
            $this->repOrdenPagoRepository->getAll()
                ->groupBy(['banco', 'codn_funci', 'codn_fuent', 'codc_uacad', 'codc_carac', 'codn_progr'])
        );
        // dd($this->reportData);
    }

    private function convertToBaseCollection($collection)
    {
        return $collection->map(function ($item) {
            if ($item instanceof \Illuminate\Database\Eloquent\Collection) {
                $item = $item->toBase();
            }
            if ($item instanceof \Illuminate\Support\Collection) {
                return $this->convertToBaseCollection($item);
            }
            return $item;
        });
    }

    public function render()
    {
        return view('livewire.reportes.orden-pago-reporte', ['reportData' => $this->reportData]);
    }
}
