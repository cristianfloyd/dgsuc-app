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
        public ?int $nro_orimp = null,
        public ?string $tipoescalafon = null,
        public ?int $nrogrupoesc = null,
        public ?string $codigoescalafon = null,
        public ?string $codc_regio = null,
        public ?string $codc_uacad = null,
        public ?string $codn_area = null,
        public ?string $codn_subar = null,
        public ?string $codn_fuent = null,
        public ?string $codn_progr = null,
        public ?string $codn_subpr = null,
        public ?string $codn_proye = null,
        public ?string $codn_activ = null,
        public ?string $codn_obra = null,
        public ?string $codn_final = null,
        public ?string $codn_funci = null,
        public ?int $ano_retro = null,
        public ?int $mes_retro = null,
        public ?string $detallenovedad = null,
        public ?string $codn_grupo_presup = null,
        public ?string $tipo_ejercicio = null,
        public ?string $codn_subsubar = null
    ) {}

    public static function rules($context): array
    {
        return [
            'id_liquidacion' => ['nullable', 'integer'],
            'nro_liqui' => ['nullable', 'integer'],
            'nro_legaj' => ['nullable', 'integer'],
            'nro_cargo' => ['nullable', 'integer'],
            'codn_conce' => ['nullable', 'integer'],
            'impp_conce' => ['nullable', 'numeric'],
            'tipo_conce' => ['nullable', 'string', 'size:1'],
            'nov1_conce' => ['nullable', 'numeric'],
            'nov2_conce' => ['nullable', 'numeric'],
            'nro_orimp' => ['nullable', 'integer'],
            'tipoescalafon' => ['nullable', 'string'],
            'nrogrupoesc' => ['nullable', 'integer'],
            'codigoescalafon' => ['nullable', 'string'],
            'codc_regio' => ['nullable', 'string'],
            'codc_uacad' => ['nullable', 'string'],
            'codn_area' => ['nullable', 'string'],
            'codn_subar' => ['nullable', 'string'],
            'codn_fuent' => ['nullable', 'string'],
            'codn_progr' => ['nullable', 'string'],
            'codn_subpr' => ['nullable', 'string'],
            'codn_proye' => ['nullable', 'string'],
            'codn_activ' => ['nullable', 'string'],
            'codn_obra' => ['nullable', 'string'],
            'codn_final' => ['nullable', 'string'],
            'codn_funci' => ['nullable', 'string'],
            'ano_retro' => ['nullable', 'integer'],
            'mes_retro' => ['nullable', 'integer'],
            'detallenovedad' => ['nullable', 'string'],
            'codn_grupo_presup' => ['nullable', 'string'],
            'tipo_ejercicio' => ['nullable', 'string'],
            'codn_subsubar' => ['nullable', 'string']
        ];
    }
}
