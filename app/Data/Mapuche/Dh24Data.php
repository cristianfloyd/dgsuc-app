<?php

declare(strict_types=1);

namespace App\Data\Mapuche;

use Spatie\LaravelData\Data;

class Dh24Data extends Data
{
    public function __construct(
        public readonly int $nro_cargo,
        public readonly int $codn_progr,
        public readonly int $codn_subpr,
        public readonly int $codn_proye,
        public readonly int $codn_activ,
        public readonly int $codn_obra,
        public readonly int $codn_fuent,
        public readonly ?float $porc_ipres,
        public readonly int $codn_area,
        public readonly int $codn_subar,
        public readonly int $codn_final,
        public readonly int $codn_funci,
        public readonly int $codn_grupo_presup = 1,
        public readonly string $tipo_ejercicio = 'A',
        public readonly int $codn_subsubar = 0,
    ) {
    }

    public static function rules($context = null): array
    {
        return [
            'nro_cargo' => ['required', 'integer'],
            'codn_progr' => ['required', 'integer'],
            'codn_subpr' => ['required', 'integer'],
            'codn_proye' => ['required', 'integer'],
            'codn_activ' => ['required', 'integer'],
            'codn_obra' => ['required', 'integer'],
            'codn_fuent' => ['required', 'integer'],
            'porc_ipres' => ['nullable', 'numeric', 'between:0,100'],
            'codn_area' => ['required', 'integer'],
            'codn_subar' => ['required', 'integer'],
            'codn_final' => ['required', 'integer'],
            'codn_funci' => ['required', 'integer'],
            'codn_grupo_presup' => ['required', 'integer'],
            'tipo_ejercicio' => ['required', 'string', 'size:1'],
            'codn_subsubar' => ['required', 'integer'],
        ];
    }
}
