<?php

namespace App\Data\Mapuche;

use Spatie\LaravelData\Data;

class Dh21hData extends Data
{
    public function __construct(
        public ?int $id_liquidacion = null,
        public ?int $nro_liqui = null,
        public ?int $nro_legaj = null,
        public ?int $nro_cargo = null,
        public ?int $codn_conce = null,
        public ?float $impp_conce = null,
        public ?string $tipo_conce = null,
        public ?float $nov1_conce = null,
        public ?float $nov2_conce = null,
    ) {
    }

    public static function rules($context): array
    {
        return [
            'nro_liqui' => ['nullable', 'integer'],
            'nro_legaj' => ['nullable', 'integer'],
            'nro_cargo' => ['nullable', 'integer'],
            'impp_conce' => ['nullable', 'numeric'],
            'tipo_conce' => ['nullable', 'string', 'size:1'],
            // ... resto de reglas
        ];
    }
}
