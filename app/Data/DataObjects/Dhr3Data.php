<?php

declare(strict_types=1);

namespace App\Data\DataObjects;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\IntegerType;

class Dhr3Data extends Data
{
    public function __construct(
        #[IntegerType]
        public readonly int $nro_liqui,

        #[IntegerType]
        public readonly int $nro_legaj,

        #[IntegerType]
        public readonly int $nro_cargo,

        #[StringType]
        public readonly string $codc_hhdd,

        #[IntegerType]
        public readonly int $nro_renglo,

        #[IntegerType]
        public readonly ?int $nro_conce,

        #[StringType]
        public readonly ?string $desc_conc,

        #[Numeric]
        public readonly ?float $novedad1,

        #[Numeric]
        public readonly ?float $novedad2,

        #[Numeric]
        public readonly ?float $impo_conc,

        #[IntegerType]
        public readonly ?int $ano_retro,

        #[IntegerType]
        public readonly ?int $mes_retro,

        #[IntegerType]
        public readonly ?int $nro_recibo,

        #[StringType]
        public readonly ?string $observa,

        #[StringType]
        public readonly ?string $tipo_conce,
    ) {}
}
