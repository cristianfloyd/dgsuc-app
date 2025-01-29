<?php

declare(strict_types=1);

namespace App\Data\Mapuche;

use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;

class Dh16Data extends Data
{
    public function __construct(
        #[Required]
        #[IntegerType]
        public readonly int $codn_grupo,

        #[Required]
        #[IntegerType]
        public readonly int $codn_conce,
    ) {}

    public static function rules(): array
    {
        return [
            'codn_grupo' => ['required', 'integer'],
            'codn_conce' => ['required', 'integer'],
        ];
    }

    public static function messages(): array
    {
        return [
            'codn_grupo.required' => 'El código de grupo es requerido',
            'codn_grupo.integer' => 'El código de grupo debe ser un número entero',
            'codn_conce.required' => 'El código de concepto es requerido',
            'codn_conce.integer' => 'El código de concepto debe ser un número entero',
        ];
    }
}
