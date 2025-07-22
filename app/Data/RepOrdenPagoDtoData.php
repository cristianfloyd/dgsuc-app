<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class RepOrdenPagoDtoData extends Data
{
    /**
     * Constructor del DTO para RepOrdenPago.
     *
     * @param int $nroLiqui Número de liquidación
     * @param int $banco Código del banco
     * @param string $codnFunci Código de función
     * @param string $codnFuent Código de fuente
     * @param string $codcUacad Código de unidad académica
     * @param string $caracter Caracter
     * @param string $codnProgr Código de programa
     * @param float $remunerativo Monto remunerativo
     * @param float $noRemunerativo Monto no remunerativo
     * @param float $otrosNoRemunerativo Otros montos no remunerativos
     * @param float $bruto Monto bruto
     * @param float $descuentos Descuentos
     * @param float $aportes Aportes
     * @param float $sueldo Sueldo
     * @param float $neto Monto neto
     * @param float $estipendio Estipendio
     * @param float $medResid Médicos residentes
     * @param float $productividad Productividad
     * @param float $salFam Salario familiar
     * @param float $hsExtras Horas extras
     * @param float $total Total
     * @param float $impGasto Importe de gasto
     */
    public function __construct(
        #[Required, Min(1)]
        public readonly int $nroLiqui,
        #[Required, Min(1)]
        public readonly int $banco,
        #[Required]
        public readonly string $codnFunci,
        #[Required]
        public readonly string $codnFuent,
        #[Required]
        public readonly string $codcUacad,
        #[Required]
        public readonly string $caracter,
        #[Required]
        public readonly string $codnProgr,
        #[Required, Min(0)]
        public readonly float $remunerativo,
        #[Required, Min(0)]
        public readonly float $noRemunerativo,
        #[Required, Min(0)]
        public readonly float $otrosNoRemunerativo,
        #[Required, Min(0)]
        public readonly float $bruto,
        #[Required, Min(0)]
        public readonly float $descuentos,
        #[Required, Min(0)]
        public readonly float $aportes,
        #[Required, Min(0)]
        public readonly float $sueldo,
        #[Required, Min(0)]
        public readonly float $neto,
        #[Required, Min(0)]
        public readonly float $estipendio,
        #[Required, Min(0)]
        public readonly float $medResid,
        #[Required, Min(0)]
        public readonly float $productividad,
        #[Required, Min(0)]
        public readonly float $salFam,
        #[Required, Min(0)]
        public readonly float $hsExtras,
        #[Required, Min(0)]
        public readonly float $total,
        #[Required, Min(0)]
        public readonly float $impGasto,
    ) {
    }

    /**
     * Convierte el DTO a un array con las claves en snake_case
     * para que coincidan con los nombres de columnas en la base de datos.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'nro_liqui' => $this->nroLiqui,
            'banco' => $this->banco,
            'codn_funci' => $this->codnFunci,
            'codn_fuent' => $this->codnFuent,
            'codc_uacad' => $this->codcUacad,
            'caracter' => $this->caracter,
            'codn_progr' => $this->codnProgr,
            'remunerativo' => $this->remunerativo,
            'no_remunerativo' => $this->noRemunerativo,
            'otros_no_remunerativo' => $this->otrosNoRemunerativo,
            'bruto' => $this->bruto,
            'descuentos' => $this->descuentos,
            'aportes' => $this->aportes,
            'sueldo' => $this->sueldo,
            'neto' => $this->neto,
            'estipendio' => $this->estipendio,
            'med_resid' => $this->medResid,
            'productividad' => $this->productividad,
            'sal_fam' => $this->salFam,
            'hs_extras' => $this->hsExtras,
            'total' => $this->total,
            'imp_gasto' => $this->impGasto,
        ];
    }
}
