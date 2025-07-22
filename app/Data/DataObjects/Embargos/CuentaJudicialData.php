<?php

namespace App\Data\DataObjects\Embargos;

use Spatie\LaravelData\Data;

class CuentaJudicialData extends Data
{
    public function __construct(
        public readonly string $nro_cuenta_judicial,
        public readonly string $tipo_cuenta,
        public readonly int $digito_verificador,
        public readonly string $titular,
        public readonly int $codigo_sucursal,
        public readonly int $nrovalorpago,
        public readonly int $nroentidadbancaria,
        public readonly ?string $cbu = null,
    ) {
    }
}
