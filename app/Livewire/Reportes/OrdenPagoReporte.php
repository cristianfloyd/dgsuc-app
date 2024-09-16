<?php

namespace App\Livewire\Reportes;

use App\Contracts\RepOrdenPagoRepositoryInterface;
use App\DTOs\ReportHeaderDTO;
use App\Services\ReportHeaderService;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Component;

class OrdenPagoReporte extends Component implements Htmlable
{
    public $reportData;
    public $totalGeneral;
    public $liquidacionId = null;
    public $totalesPorFormaPago = [];
    public array $reportHeader;

    protected $repOrdenPagoRepository;

    public function boot(RepOrdenPagoRepositoryInterface $repOrdenPagoRepository, ReportHeaderService $reportHeaderService)
    {
        $this->repOrdenPagoRepository = $repOrdenPagoRepository;
        $dto = $reportHeaderService->getReportHeader($this->getLiquidationNumber());
        $this->reportHeader = [
            'logoPath' => $dto->logoPath,
            'orderNumber' => $dto->orderNumber,
            'liquidationNumber' => $dto->liquidationNumber,
            'liquidationDescription' => $dto->liquidationDescription,
            'generationDate' => $dto->generationDate,
        ];
    }



    public function mount(int $liquidacionId = null)
    {
        $this->liquidacionId = $liquidacionId;
        $this->loadReportData($this->liquidacionId);
        $this->calculateTotalesGenerales();
    }


    public function loadReportData(array|int|null $liquidacionId = null)
    {
        $data = $this->repOrdenPagoRepository->getAll($liquidacionId);

        // Asegurar que $data sea una colección
        if (!$data instanceof \Illuminate\Support\Collection) {
            $data = collect($data);
            dd($data);
        }

        $this->reportData = $this->convertToBaseCollection(
            collection: $data->groupBy('banco')

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
                                                    $this->addTotals( totalAcumulado: $totalUacad,  totalsToAdd: $totals);
                                                    $this->addTotals( totalAcumulado: $totalFuente,  totalsToAdd: $totals);
                                                    $this->addTotals( totalAcumulado: $totalFuncion,  totalsToAdd: $totals);
                                                    $this->addTotals( totalAcumulado: $totalBanco,  totalsToAdd: $totals);

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
     * Obtiene el número de liquidación.
     *
     * Este método privado devuelve el número de liquidación, que se obtiene a partir de la propiedad `$liquidacionId`. Si `$liquidacionId` es nulo, se devuelve el valor predeterminado de 1.
     *
     * @return int El número de liquidación.
     */
    private function getLiquidationNumber(): int
    {
        return $this->liquidacionId ?? 1;
    }

    /**
     * Suma los totales de un array a otro array de totales.
     *
     * @param array &$totalAcumulado Array donde se acumularán los totales
     * @param array $totalsToAdd Array con los totales a sumar
     */
    private function addTotals(&$totalAcumulado, $totalsToAdd)
    {
        foreach ($totalAcumulado as $key => $value) {
            $totalAcumulado[$key] += $totalsToAdd[$key];
        }
    }

    private function calculateTotalGeneral()
    {
        $this->totalGeneral = [
            'remunerativo' => 0,
            'estipendio' => 0,
            'productividad' => 0,
            'med_resid' => 0,
            'sal_fam' => 0,
            'hs_extras' => 0,
            'total' => 0
        ];

        foreach ($this->reportData as $banco) {
            $this->addTotals($this->totalGeneral, $banco['totalBanco']);
        }
    }

    public function calculateTotalesGenerales()
    {
        $this->totalesPorFormaPago = [
            'banco' => [],
            'efectivo' => []
        ];

        foreach ($this->reportData as $banco => $porBanco) {
            $formaPago = $banco == '1' ? 'banco' : 'efectivo';

            foreach ($porBanco['funciones'] as $funcion => $porFuncion) {
                foreach ($porFuncion['fuentes'] as $fuente => $porFuente) {
                    if (!isset($this->totalesPorFormaPago[$formaPago][$funcion][$fuente])) {
                        $this->totalesPorFormaPago[$formaPago][$funcion][$fuente] = $this->initializeTotals();
                    }
                    $this->addTotals($this->totalesPorFormaPago[$formaPago][$funcion][$fuente], $porFuente['totalFuente']);
                }
            }
        }

        $this->totalGeneral = $this->initializeTotals();
        foreach (['banco', 'efectivo'] as $formaPago) {
            foreach ($this->totalesPorFormaPago[$formaPago] as $funcionTotals) {
                foreach ($funcionTotals as $fuenteTotals) {
                    $this->addTotals($this->totalGeneral, $fuenteTotals);
                }
            }
        }
    }

    /**
     * Calcula el total por función sumando los totales de todas las fuentes de financiamiento.
     *
     * @param array $porFuncion Array con los datos de una función específica
     * @return array Array con los totales calculados para la función
     */
    public function calcularTotalPorFuncion($porFuncion): array
    {
        $total = $this->initializeTotals();
        foreach ($porFuncion as $fuenteTotals) {
            $this->addTotals($total, $fuenteTotals);
        }
        return $total;
    }

    /**
     * Calcula el total por forma de pago sumando los totales de todas las funciones.
     *
     * @param array $formaPagoData Array con los datos de una forma de pago específica
     * @return array Array con los totales calculados para la forma de pago
     */
    public function calcularTotalPorFormaPago($formaPagoData): array
    {
        $total = $this->initializeTotals();
        foreach ($formaPagoData as $funcionData) {
            foreach ($funcionData as $fuenteTotals) {
                $this->addTotals($total, $fuenteTotals);
            }
        }
        return $total;
    }

    public function toHtml()
    {
        return $this->render()->with($this->getPublicProperties());
    }

    public function render()
    {
        return view(view: 'livewire.reportes.orden-pago-reporte', data: ['reportData' => $this->reportData]);
    }
}
