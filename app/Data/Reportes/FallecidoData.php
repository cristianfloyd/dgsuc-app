<?php

declare(strict_types=1);

namespace App\Data\Reportes;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

class FallecidoData extends Data
{
    public function __construct(
        public readonly int $nro_legaj,
        public readonly string $apellido,
        public readonly string $nombre,
        public readonly string $cuil,
        public readonly string $codc_uacad,
        #[WithCast(DateTimeInterfaceCast::class)]
        public readonly ?Carbon $fec_defun,
    ) {
    }

    public static function rules($context = null): array
    {
        return [
            'nro_legaj' => ['required', 'integer'],
            'apellido' => ['required', 'string', 'max:20'],
            'nombre' => ['required', 'string', 'max:20'],
            'cuil' => ['required', 'string', 'regex:/^\d{2}-\d{8}-\d{1}$/'],
            'codc_uacad' => ['required', 'string', 'size:4'],
            'fec_defun' => ['nullable', 'date'],
        ];
    }

    public static function messages(...$args): array
    {
        return [
            'cuil.regex' => 'El CUIL debe tener el formato XX-XXXXXXXX-X',
            'codc_uacad.size' => 'El código de unidad académica debe tener exactamente 4 caracteres',
        ];
    }
}
