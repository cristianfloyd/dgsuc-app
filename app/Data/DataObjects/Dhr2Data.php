<?php

namespace App\Data\DataObjects;

use Spatie\LaravelData\Data;

class Dhr2Data extends Data
{
    public function __construct(
        public readonly int $nro_liqui,
        public readonly int $nro_legaj,
        public readonly int $nro_cargo,
        public readonly ?string $desc_apyno = null,
        public readonly ?string $tipo_docum = null,
        public readonly ?int $nro_docum = null,
        public readonly ?float $tot_haber = null,
        public readonly ?float $tot_reten = null,
        public readonly ?float $tot_neto = null,
        public readonly ?bool $anulado = false,
        public readonly ?bool $impreso = false,
    ) {
    }
}
