<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\IntegerType;

class RepEmbarazadaData extends Data
{
    public function __construct(
        #[Required]
        #[IntegerType]
        public readonly int $nro_legaj,

        #[Required]
        #[StringType]
        #[MapInputName('apellido')]
        public readonly string $apellido,

        #[Required]
        #[StringType]
        public readonly string $nombre,

        #[Required]
        #[StringType]
        public readonly string $cuil,

        #[Required]
        #[StringType]
        public readonly string $codc_uacad,
    ) {}
}
