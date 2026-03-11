<?php

namespace App\Data\DataObjects\Embargos;

use Spatie\LaravelData\Data;

class TipoJuicioData extends Data
{
    public function __construct(
        public readonly string $desc_tipo_juicio,
        public readonly ?int $id_tipo_juicio = null,
    ) {}
}
