<?php

namespace App\Livewire\Reportes;

use Livewire\Component;

class OrdenPagoReporteExportable extends Component
{
    public $totalGeneral;

    public $reportData;
    public $reportHeader;
    public $totalesPorFormaPago;

    public function mount($reportData, $reportHeader, $totalesPorFormaPago)
    {
        $this->reportData = $reportData;
        $this->reportHeader = $reportHeader;
        $this->totalesPorFormaPago = $totalesPorFormaPago;
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

    public function render()
    {
        return view('livewire.reportes.orden-pago-reporte-exportable');
    }
}
