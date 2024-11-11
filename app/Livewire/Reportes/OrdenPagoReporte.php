<?php

namespace App\Livewire\Reportes;

use App\Contracts\RepOrdenPagoRepositoryInterface;
use App\Services\ReportHeaderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class OrdenPagoReporte extends Component implements Htmlable
{
    public $reportData;
    public $liquidacionId = null;
    public $totalGeneral;
    public array $totalesPorFormaPago = [];
    public $totalesPorFuncion; // propiedad para almacenar los totales por función
    public array $totalesPorFinanciamiento = [];
    public array $reportHeader;

    protected RepOrdenPagoRepositoryInterface $repOrdenPagoRepository;

    public function boot(RepOrdenPagoRepositoryInterface $repOrdenPagoRepository, ReportHeaderService $reportHeaderService): void
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

    public function mount(int $liquidacionId = null): void
    {
        $this->liquidacionId = $liquidacionId;
        $this->loadReportData($this->liquidacionId);
        $this->calculateTotalesGenerales();
    }

    /**
     * Carga los datos del reporte de Orden de Pago.
     *
     * Este método publico se encarga de cargar los datos del reporte de Orden de Pago,
     * agrupándolos por banco, función, fuente de financiamiento y unidad académica.
     * También calcula los totales acumulados para cada nivel de agrupación.
     *
     * @param array|int|null $liquidacionId El ID de la liquidación para la que se generará el reporte.
     * @return void
     */
    public function loadReportData(array|int|null $liquidacionId = null): void
    {
        $data = $this->repOrdenPagoRepository->getAllWithUnidadAcademica($liquidacionId);
        // Asegurar que $data sea una colección
        if (!$data instanceof Collection) {
            $data = collect($data);
        }

        $this->reportData = $this->convertToBaseCollection(
            collection: $data->groupBy('banco')
                ->map(function ($porBanco) {

                    $totalBanco = $this->initializeTotals();
                    $funciones = $porBanco->groupBy('codn_funci')
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
                                                    $this->addTotals(totalAcumulado: $totalUacad, totalsToAdd: $totals);
                                                    $this->addTotals(totalAcumulado: $totalFuente, totalsToAdd: $totals);
                                                    $this->addTotals(totalAcumulado: $totalFuncion, totalsToAdd: $totals);
                                                    $this->addTotals(totalAcumulado: $totalBanco, totalsToAdd: $totals);

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
    }

    /**
     * Convierte una colección a una colección base.
     *
     * Este método privado toma una colección y convierte cada elemento a una colección base.
     * Si el elemento es una instancia de \Illuminate\Database\Eloquent\Collection, se convierte a una colección base.
     * Si el elemento es una instancia de \Illuminate\Support\Collection, se llama recursivamente a este método para convertir la colección.
     *
     * @param Collection $collection La colección a convertir.
     * @return Collection La colección convertida a una colección base.
     */
    private function convertToBaseCollection(Collection $collection): Collection
    {
        return $collection->map(function ($item) {
            if ($item instanceof Collection) {
                $item = $item->toBase();
            }
            if ($item instanceof Collection) {
                return $this->convertToBaseCollection($item);
            }
            return $item;
        });
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
    private function initializeTotals(): array
    {
        return [
            'bruto' => 0,
            'estipendio' => 0,
            'productividad' => 0,
            'med_resid' => 0,
            'sal_fam' => 0,
            'hs_extras' => 0,
            'total' => 0
        ];
    }

    /**
     * Calcula los totales acumulados para un conjunto de elementos.
     *
     * Este método privado toma un conjunto de elementos y calcula los totales
     * acumulados para los siguientes campos: remunerativo, estipendio, productividad,
     * med_resid, sal_fam, hs_extras y el total.
     *
     * @param Collection $items Conjunto de elementos para los que se calcularán los totales.
     * @return array Un array asociativo con los totales calculados para cada campo.
     */
    private function calculateTotals(Collection $items): array
    {
        return [
            'bruto' => $items->sum('bruto'),
            'estipendio' => $items->sum('estipendio'),
            'productividad' => $items->sum('productividad'),
            'med_resid' => $items->sum('med_resid'),
            'sal_fam' => $items->sum('sal_fam'),
            'hs_extras' => $items->sum('hs_extras'),
            'total' => $items->sum('total')
        ];
    }

    /**
     * Suma los totales de un array a otro array de totales.
     *
     * @param array &$totalAcumulado Array donde se acumularán los totales
     * @param array $totalsToAdd Array con los totales a sumar
     */
    private function addTotals(array &$totalAcumulado, array $totalsToAdd): void
    {
        foreach ($totalAcumulado as $key => $value) {
            $totalAcumulado[$key] += $totalsToAdd[$key];
        }
    }

    public function calculateTotalesGenerales(): void
    {
        // Inicializar la estructura de datos
        $this->totalesPorFinanciamiento = ['banco' => [], 'efectivo' => []];
        $this->totalesPorFuncion = ['banco' => [], 'efectivo' => []];
        $this->totalesPorFormaPago = ['banco' => $this->initializeTotals(), 'efectivo' => $this->initializeTotals()];

        foreach ($this->reportData as $banco => $porBanco) {
            $formaPago = $banco == '1' ? 'banco' : 'efectivo';

            foreach ($porBanco['funciones'] as $funcion => $porFuncion) {
                if (!isset($this->totalesPorFuncion[$formaPago][$funcion])) {
                    $this->totalesPorFuncion[$formaPago][$funcion] = $this->initializeTotals();
                }

                foreach ($porFuncion['fuentes'] as $fuente => $porFuente) {
                    if (!isset($this->totalesPorFinanciamiento[$formaPago][$funcion][$fuente])) {
                        $this->totalesPorFinanciamiento[$formaPago][$funcion][$fuente] = $this->initializeTotals();
                    }
                    $this->addTotals($this->totalesPorFinanciamiento[$formaPago][$funcion][$fuente], $porFuente['totalFuente']);
                    $this->addTotals($this->totalesPorFuncion[$formaPago][$funcion], $porFuente['totalFuente']);
                    $this->addTotals($this->totalesPorFormaPago[$formaPago], $porFuente['totalFuente']);
                }
            }
        }

        $this->totalGeneral = $this->initializeTotals();
        foreach (['banco', 'efectivo'] as $formaPago) {
            $this->addTotals($this->totalGeneral, $this->totalesPorFormaPago[$formaPago]);
        }
    }


    public function descargarReportePDF(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $data = [
            'reportData' => $this->reportData,
            'reportHeader' => $this->reportHeader,
            'totalesPorFormaPago' => $this->totalesPorFormaPago,
            'totalesPorFuncion' => $this->totalesPorFuncion,
            'totalesPorFinanciamiento' => $this->totalesPorFinanciamiento,
            'totalGeneral' => $this->totalGeneral,
        ];

        $view = view('livewire.reportes.orden-pago-reporte-exportable')->with($data);
        $html = $view->render();
        $pdfss = Pdf::loadHTML($html)->setPaper('a4', 'landscape');


        //$pdf = Pdf::loadView(view: 'livewire.reportes.orden-pago-reporte-exportable', data: $data);


        Log::info('PDF generado');

        return response()->streamDownload(function () use ($pdfss) {
            echo $pdfss->stream();
        }, 'users.pdf');
    }

    public function render(): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application
    {
        return view(view: 'livewire.reportes.orden-pago-reporte-exportable', data: [
            'reportData' => $this->reportData,
            'reportHeader' => $this->reportHeader,
            'totalesPorFormaPago' => $this->totalesPorFormaPago,
            'totalGeneral' => $this->totalGeneral,
        ]);
    }

    public function toHtml()
    {
        return $this->render()->with($this->getPublicProperties());
    }

    private function getPublicProperties(): array
    {
        return [];
    }
}
