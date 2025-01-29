<?php

declare(strict_types=1);

namespace App\Data\Mapuche;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Ramsey\Uuid\Type\Integer;
use phpDocumentor\Reflection\Types\Boolean;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\StringType;

class Dh09Data extends Data
{
    public function __construct(
        #[Integer]
        public readonly int $nro_legaj,

        #[Integer, Nullable]
        public readonly ?int $vig_otano,

        #[Integer, Nullable]
        public readonly ?int $vig_otmes,

        #[Integer, Nullable]
        public readonly ?int $nro_tab02,

        #[StringType(max: 4), Nullable]
        public readonly ?string $codc_estcv,

        #[Boolean]
        public readonly bool $sino_embargo,

        #[StringType(max: 1), Nullable]
        public readonly ?string $sino_otsal,

        #[StringType(max: 1), Nullable]
        public readonly ?string $sino_jubil,

        #[Integer, Nullable]
        public readonly ?int $nro_tab08,

        #[StringType(max: 4), Nullable]
        public readonly ?string $codc_bprev,

        #[Integer, Nullable]
        public readonly ?int $nro_tab09,

        #[StringType(max: 4), Nullable]
        public readonly ?string $codc_obsoc,

        #[StringType(max: 15), Nullable]
        public readonly ?string $nro_afili,

        #[Date, Nullable]
        public readonly ?Carbon $fec_altos,

        #[Date, Nullable]
        public readonly ?Carbon $fec_endjp,

        #[StringType(max: 20), Nullable]
        public readonly ?string $desc_envio,

        #[Integer, Nullable]
        public readonly ?int $cant_cargo,

        #[StringType(max: 40), Nullable]
        public readonly ?string $desc_tarea,

        #[StringType(max: 4), Nullable]
        public readonly ?string $codc_regio,

        #[StringType(max: 4), Nullable]
        public readonly ?string $codc_uacad,

        #[Date, Nullable]
        public readonly ?Carbon $fec_vtosf,

        #[Date, Nullable]
        public readonly ?Carbon $fec_reasf,

        #[Date, Nullable]
        public readonly ?Carbon $fec_defun,

        #[Date, Nullable]
        public readonly ?Carbon $fecha_jubilacion,

        #[Date, Nullable]
        public readonly ?Carbon $fecha_grado,

        #[Integer, Nullable]
        public readonly ?int $nro_agremiacion,

        #[Date, Nullable]
        public readonly ?Carbon $fecha_permanencia,

        #[StringType(max: 4), Nullable]
        public readonly ?string $ua_asigfamiliar,

        #[Date, Nullable]
        public readonly ?Carbon $fechadjur894,

        #[StringType(max: 1), Nullable]
        public readonly ?string $renunciadj894,

        #[Date, Nullable]
        public readonly ?Carbon $fechadechere,

        #[StringType(max: 4), Nullable]
        public readonly ?string $coddependesemp,

        #[Integer, Nullable]
        public readonly ?int $conyugedependiente,

        #[Date, Nullable]
        public readonly ?Carbon $fec_ingreso,

        #[StringType(max: 4), Nullable]
        public readonly ?string $codc_uacad_seguro,

        #[Date, Nullable]
        public readonly ?Carbon $fecha_recibo,

        #[StringType(max: 20), Nullable]
        public readonly ?string $tipo_norma,

        #[Integer, Nullable]
        public readonly ?int $nro_norma,

        #[StringType(max: 20), Nullable]
        public readonly ?string $tipo_emite,

        #[Date, Nullable]
        public readonly ?Carbon $fec_norma,

        #[Boolean]
        public readonly bool $fuerza_reparto = false,
    ) {}

    public static function rules(\Spatie\LaravelData\Support\Validation\ValidationContext $context): array
    {
        return [
            'nro_legaj' => ['required', 'integer'],
            'vig_otano' => ['nullable', 'integer'],
            'vig_otmes' => ['nullable', 'integer', 'between:1,12'],
            'nro_tab02' => ['nullable', 'integer'],
            'codc_estcv' => ['nullable', 'string', 'max:4'],
            'sino_embargo' => ['required', 'boolean'],
            'sino_otsal' => ['nullable', 'string', 'size:1'],
            'sino_jubil' => ['nullable', 'string', 'size:1'],
            'nro_tab08' => ['nullable', 'integer'],
            'codc_bprev' => ['nullable', 'string', 'max:4'],
            'nro_tab09' => ['nullable', 'integer'],
            'codc_obsoc' => ['nullable', 'string', 'max:4'],
            'nro_afili' => ['nullable', 'string', 'max:15'],
            'fec_altos' => ['nullable', 'date'],
            'fec_endjp' => ['nullable', 'date'],
            'desc_envio' => ['nullable', 'string', 'max:20'],
            'cant_cargo' => ['nullable', 'integer'],
            'desc_tarea' => ['nullable', 'string', 'max:40'],
            'codc_regio' => ['nullable', 'string', 'max:4'],
            'codc_uacad' => ['nullable', 'string', 'max:4'],
            'fec_vtosf' => ['nullable', 'date'],
            'fec_reasf' => ['nullable', 'date'],
            'fec_defun' => ['nullable', 'date'],
            'fecha_jubilacion' => ['nullable', 'date'],
            'fecha_grado' => ['nullable', 'date'],
            'nro_agremiacion' => ['nullable', 'integer'],
            'fecha_permanencia' => ['nullable', 'date'],
            'ua_asigfamiliar' => ['nullable', 'string', 'max:4'],
            'fechadjur894' => ['nullable', 'date'],
            'renunciadj894' => ['nullable', 'string', 'size:1'],
            'fechadechere' => ['nullable', 'date'],
            'coddependesemp' => ['nullable', 'string', 'max:4'],
            'conyugedependiente' => ['nullable', 'integer'],
            'fec_ingreso' => ['nullable', 'date'],
            'codc_uacad_seguro' => ['nullable', 'string', 'max:4'],
            'fecha_recibo' => ['nullable', 'date'],
            'tipo_norma' => ['nullable', 'string', 'max:20'],
            'nro_norma' => ['nullable', 'integer'],
            'tipo_emite' => ['nullable', 'string', 'max:20'],
            'fec_norma' => ['nullable', 'date'],
            'fuerza_reparto' => ['required', 'boolean']
        ];
    }

    public static function messages(...$args): array
    {
        return [
            'nro_legaj.required' => 'El número de legajo es obligatorio',
            'vig_otmes.between' => 'El mes debe estar entre 1 y 12',
            'sino_embargo.required' => 'El campo embargo es obligatorio',
            'sino_otsal.size' => 'El indicador debe ser de un solo carácter',
            'sino_jubil.size' => 'El indicador debe ser de un solo carácter',
        ];
    }
}
