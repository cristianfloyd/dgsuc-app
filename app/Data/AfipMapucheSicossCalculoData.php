<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class AfipMapucheSicossCalculoData extends Data
{
    public function __construct(
        public readonly string $cuil,
        public readonly float $remtotal,
        public readonly float $rem1,
        public readonly float $rem2,
        public readonly float $aportesijp,
        public readonly float $aporteinssjp,
        public readonly float $contribucionsijp,
        public readonly float $contribucioninssjp,
        public readonly float $aportediferencialsijp,
        public readonly float $aportesres33_41re,
        public readonly string $codc_uacad,
        public readonly string $caracter,
    ) {
    }
}
