<?php

namespace App\Data\DataObjects\Embargos;

use Spatie\LaravelData\Data;

class TipoExpedienteData extends Data
{
    public function __construct(
        public readonly string $desc_tipo_expediente,
        public readonly ?int $id_tipo_expediente = null,
    ) {
    }
}
