<?php

namespace App\Data\Mapuche;

use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class RepGerencialFinalData extends Data
{
    public function __construct(
        #[Required, Numeric]
        public readonly int $codn_fuent,
        #[Required, Numeric]
        public readonly int $codn_depen,
        #[Required]
        public readonly string $tipo_ejercicio,
        #[Required]
        public readonly string $codn_grupo_presup,
        #[Required, Numeric]
        public readonly float $imp_gasto,
        #[Required, Numeric]
        public readonly float $imp_bruto,
        #[Required, Numeric]
        public readonly float $imp_neto,
        public readonly ?string $cuil = null,
    ) {
    }

    public static function rules(ValidationContext $context = null): array
    {
        return [
            'codn_fuent' => ['required', 'integer'],
            'codn_depen' => ['required', 'integer'],
            'tipo_ejercicio' => ['required', 'string'],
            'imp_gasto' => ['required', 'numeric', 'min:0'],
            'imp_bruto' => ['required', 'numeric', 'min:0'],
            'imp_neto' => ['required', 'numeric', 'min:0'],
            'cuil' => ['nullable', 'string', 'size:11'],
        ];
    }
}
