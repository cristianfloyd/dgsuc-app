<?php

namespace App\Livewire\Reportes;

use App\Models\Reportes\OrdenPagoMapuche;
use Livewire\Component;

class OrdenPagoReporte extends Component
{
    public $reportData;

    public function mount()
    {
        $this->loadReportData();
    }


    public function loadReportData()
    {
        $ordenPagoModel = new OrdenPagoMapuche();
        $this->reportData = $this->convertToBaseCollection(
            $ordenPagoModel->getOrdenPago()
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
