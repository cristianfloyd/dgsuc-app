<?php

namespace App\Data\DataObjects;

use Spatie\LaravelData\Data;

class EmbEstadoEmbargoData extends Data
{
    public function __construct(
        public readonly string $desc_estado_embargo,
        public readonly ?int $id_estado_embargo = null,
    ) {}
}
