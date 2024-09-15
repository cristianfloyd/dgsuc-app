<?php

namespace App\Livewire\Reportes;

use App\Contracts\RepOrdenPagoRepositoryInterface;
use Livewire\Component;

class OrdenPagoReporte extends Component
{
    public $reportData;
    protected $repOrdenPagoRepository;

    public function boot(RepOrdenPagoRepositoryInterface $repOrdenPagoRepository)
    {
        $this->repOrdenPagoRepository = $repOrdenPagoRepository;
    }



    public function mount()
    {
        $this->loadReportData();
    }


    public function loadReportData()
    {
        $this->reportData = $this->convertToBaseCollection(
            collection: $this->repOrdenPagoRepository->getAll()
                ->groupBy('banco')

                ->map(function ($porBanco) {

                    $totalBanco = $this->initializeTotals();
                    $funciones =  $porBanco->groupBy('codn_funci')
                        ->map(function ($porFunci, $funcion) use (&$totalBanco) {

                            $totalFuncion = $this->initializeTotals();
                            $fuentes = $porFunci->groupBy('codn_fuent')
                                ->map(function ($porFuente) use (&$totalFuncion, &$totalBanco) {

                                    $totalFuente = $this->initializeTotals();
                                    $unidades = $porFuente->groupBy('codc_uacad')
                                        ->map(callback: function ($porUacad) use (&$totalFuente, &$totalFuncion, &$totalBanco) {

                                            $totalUacad = $this->initializeTotals();
                                            $caracteres = $porUacad->groupBy('caracter')

                                                ->map(function ($items) use (&$totalUacad, &$totalFuente, &$totalFuncion, &$totalBanco) {

                                                    $totals = $this->calculateTotals($items);
                                                    $this->addTotals(totalUacad: $totalUacad, totals: $totals);
                                                    $this->addTotals(totalUacad: $totalFuente, totals: $totals);
                                                    $this->addTotals(totalUacad: $totalFuncion, totals: $totals);
                                                    $this->addTotals(totalUacad: $totalBanco, totals: $totals);

                                                    $retorno = [
                                                        'items' => $items,
                                                        'totals' => $totals
                                                    ];
                                                    // dd($retorno);
                                                    return $retorno;
                                                });
                                            return [
                                                'caracteres' => $caracteres,
                                                'totalUacad' => $totalUacad
                                            ];
                                        });
                                    return [
                                        'unidades' => $unidades,
                                        'totalFuente' => $totalFuente
                                    ];
                                });
                            return [
                                'fuentes' => $fuentes,
                                'totalFuncion' => $totalFuncion
                            ];
                        });
                    return [
                        'funciones' => $funciones,
                        'totalBanco' => $totalBanco
                    ];
                })
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

    /**
     * Calcula los totales acumulados para un conjunto de elementos.
     *
     * Este método privado toma un conjunto de elementos y calcula los totales
     * acumulados para los siguientes campos: remunerativo, estipendio, productividad,
     * med_resid, sal_fam, hs_extras y el total.
     *
     * @param \Illuminate\Support\Collection $items Conjunto de elementos para los que se calcularán los totales.
     * @return array Un array asociativo con los totales calculados para cada campo.
     */
    private function calculateTotals($items)
    {
        return [
            'remunerativo' => $items->sum('remunerativo'),
            'estipendio' => $items->sum('estipendio'),
            'productividad' => $items->sum('productividad'),
            'med_resid' => $items->sum('med_resid'),
            'sal_fam' => $items->sum('sal_fam'),
            'hs_extras' => $items->sum('hs_extras'),
            'total' => $items->sum('total')
        ];
    }

    /**
     * Inicializa un array con los totales acumulados a cero.
     *
     * Este método privado inicializa un array con los campos necesarios para
     * almacenar los totales acumulados, con todos los valores establecidos a cero.
     * Este array se utiliza posteriormente para ir acumulando los totales
     * calculados para cada unidad académica y carácter.
     *
     * @return array Un array con los campos de totales inicializados a cero.
     */
    private function initializeTotals()
    {
        return [
            'remunerativo' => 0,
            'estipendio' => 0,
            'productividad' => 0,
            'med_resid' => 0,
            'sal_fam' => 0,
            'hs_extras' => 0,
            'total' => 0
        ];
    }

    /**
     * Agrega los totales acumulados a la variable $totalUacad.
     *
     * @param array $totalUacad Referencia al array de totales acumulados.
     * @param array $totals Array de totales a agregar.
     */
    private function addTotals(&$totalUacad, $totals)
    {
        foreach ($totalUacad as $key => $value) {
            $totalUacad[$key] += $totals[$key];
        }
    }

    public function render()
    {
        return view(view: 'livewire.reportes.orden-pago-reporte', data: ['reportData' => $this->reportData]);
    }
}
