<?php

namespace App\Data\Mapuche;

use Spatie\LaravelData\Data;

class Dh15Data extends Data
{
    public function __construct(
        public readonly int $codn_grupo,
        public readonly string $desc_grupo,
        public readonly int $codn_tipogrupo,
    ) {}

    public static function rules(\Spatie\LaravelData\Support\Validation\ValidationContext $context): array
    {
        return [
            'codn_grupo' => ['required', 'integer'],
            'desc_grupo' => ['required', 'string', 'max:50'],
            'codn_tipogrupo' => ['required', 'integer'],
        ];
    }
}
