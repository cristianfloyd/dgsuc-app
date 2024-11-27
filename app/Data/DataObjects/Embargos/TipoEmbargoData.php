<?php
declare(strict_types=1);

namespace App\Data\DataObjects\Embargos;

use Spatie\LaravelData\Data;

class TipoEmbargoData extends Data
{
    public function __construct(
        public readonly string $desc_tipo_embargo,
        public readonly int $codn_tipogrupo,
        public readonly int $id_tipo_remuneracion,
        public readonly int $mov_inicial_cta_cte = 0,
        public readonly ?int $codn_conce = null,
        public readonly ?int $id_tipo_embargo = null,
    ) {}
}
