<?php

declare(strict_types=1);

namespace App\Data\DataObjects\Embargos;

use Spatie\LaravelData\Data;

class TipoRemuneracionData extends Data
{
    public function __construct(
        public readonly string $desc_tipo_remuneracion,
        public readonly ?int $id_tipo_remuneracion = null,
    ) {
    }
}
