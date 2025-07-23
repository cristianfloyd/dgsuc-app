<?php

namespace App\Data\Mapuche;

use Spatie\LaravelData\Data;

class Dh35Data extends Data
{
    public function __construct(
        public readonly string $tipo_escal,
        public readonly string $codc_carac,
        public readonly ?string $desc_grupo,
        public readonly ?string $tipo_carac,
        public readonly ?int $nro_orden,
        public readonly ?int $nro_subpc,
        public readonly ?int $controlcargos,
        public readonly ?int $controlhoras,
        public readonly ?int $controlpuntos,
        public readonly bool $caracter_concursado = false,
    ) {
    }

    public static function rules($context = null): array
    {
        return [
            'tipo_escal' => ['required', 'string', 'size:1'],
            'codc_carac' => ['required', 'string', 'size:4'],
            'desc_grupo' => ['nullable', 'string', 'max:20'],
            'tipo_carac' => ['nullable', 'string', 'size:1'],
            'nro_orden' => ['nullable', 'integer', 'min:0', 'max:29'],
            'nro_subpc' => ['nullable', 'integer'],
            'controlcargos' => ['nullable', 'integer'],
            'controlhoras' => ['nullable', 'integer'],
            'controlpuntos' => ['nullable', 'integer'],
            'caracter_concursado' => ['required', 'boolean'],
        ];
    }
}
