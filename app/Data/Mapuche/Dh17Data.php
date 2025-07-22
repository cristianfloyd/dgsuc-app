<?php

declare(strict_types=1);

namespace App\Data\Mapuche;

use Spatie\LaravelData\Data;

class Dh17Data extends Data
{
    public function __construct(
        public readonly int $codn_conce,
        public readonly ?string $objt_gtope,
        public readonly ?string $objt_gtote,
        public readonly ?int $nro_prove,
    ) {
    }

    public static function rules($context): array
    {
        return [
            'codn_conce' => ['required', 'integer'],
            'objt_gtope' => ['nullable', 'string', 'max:30'],
            'objt_gtote' => ['nullable', 'string', 'max:30'],
            'nro_prove' => ['nullable', 'integer'],
        ];
    }
}
