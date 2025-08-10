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
        public ?int $codn_area = null,
        public ?int $codn_subar = null,
        public ?int $codn_fuent = null,
        public ?int $codn_progr = null,
        public ?int $codn_subpr = null,
        public ?int $codn_proye = null,
        public ?int $codn_activ = null,
        public ?int $codn_obra = null,
        public ?int $codn_final = null,
        public ?int $codn_funci = null,
        public ?int $ano_retro = null,
        public ?int $mes_retro = null,
        public ?string $detallenovedad = null,
        public ?int $codn_grupo_presup = null,
        public ?string $tipo_ejercicio = null,
        public ?int $codn_subsubar = null,
    ) {
    }

    public static function rules($context = null): array
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
            'codn_area' => ['nullable', 'integer'],
            'codn_subar' => ['nullable', 'integer'],
            'codn_fuent' => ['nullable', 'integer'],
            'codn_progr' => ['nullable', 'integer'],
            'codn_subpr' => ['nullable', 'integer'],
            'codn_proye' => ['nullable', 'integer'],
            'codn_activ' => ['nullable', 'integer'],
            'codn_obra' => ['nullable', 'integer'],
            'codn_final' => ['nullable', 'integer'],
            'codn_funci' => ['nullable', 'integer'],
            'ano_retro' => ['nullable', 'integer'],
            'mes_retro' => ['nullable', 'integer'],
            'detallenovedad' => ['nullable', 'string'],
            'codn_grupo_presup' => ['nullable', 'integer'],
            'tipo_ejercicio' => ['nullable', 'string'],
            'codn_subsubar' => ['nullable', 'integer'],
        ];
    }
}
