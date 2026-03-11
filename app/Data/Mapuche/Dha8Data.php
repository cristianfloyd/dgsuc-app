<?php

declare(strict_types=1);

namespace App\Data\Mapuche;

use Spatie\LaravelData\Data;

class Dha8Data extends Data
{
    public function __construct(
        public readonly int $nro_legajo,
        public readonly ?int $codigosituacion,
        public readonly ?int $codigocondicion,
        public readonly ?int $codigoactividad,
        public readonly ?int $codigozona,
        public readonly ?float $porcaporteadicss,
        public readonly ?int $codigomodalcontrat,
        public readonly ?string $provincialocalidad,
    ) {}
}
