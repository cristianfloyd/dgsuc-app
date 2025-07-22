<?php

declare(strict_types=1);

namespace App\Data\DataObjects;

use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class Dhr1Data extends Data
{
    public function __construct(
        #[IntegerType]
        public readonly int $nro_liqui,
        #[IntegerType]
        public readonly ?int $per_liano,
        #[IntegerType]
        public readonly ?int $per_limes,
        #[StringType]
        public readonly ?string $desc_liqui,
        #[Date]
        public readonly ?string $fec_emisi,
        #[Date]
        public readonly ?string $fec_ultap,
        #[IntegerType]
        public readonly ?int $per_anoap,
        #[IntegerType]
        public readonly ?int $per_mesap,
        #[StringType]
        public readonly ?string $desc_lugap,
        public readonly mixed $plantilla = null,
    ) {
    }
}
