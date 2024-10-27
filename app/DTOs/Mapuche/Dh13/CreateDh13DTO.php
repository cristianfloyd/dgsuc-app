<?php

namespace App\DTOs\Mapuche\Dh13;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

/**
 * DTO para la creación de registros Dh13
 */
class CreateDh13DTO extends Dh13DTO
{

    public static function rules($context): array
    {
        return [
            'codn_conce' => ['required', 'integer', 'min:1'],
            'desc_calcu' => ['nullable', 'string', 'max:250'],
            'nro_orden_formula' => ['required', 'integer', 'min:1'],
            'desc_condi' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Define mensajes personalizados para las reglas de validación
     *
     * @return array
     */
    public static function messages(...$args): array
    {
        return [
            'codn_conce.min' => 'El código de concepto debe ser positivo',
            'nro_orden_formula.min' => 'El número de orden debe ser positivo',
        ];
    }

    /**
     * Crea una instancia desde una Request
     *
     * @param Request $request Request HTTP
     * @return static Nueva instancia del DTO
     */
    public static function fromRequest(Request $request): self
    {
        return self::validateAndCreate([
            'codn_conce' => $request->integer('codn_conce'),
            'desc_calcu' => $request->string('desc_calcu'),
            'nro_orden_formula' => $request->integer('nro_orden_formula'),
            'desc_condi' => $request->string('desc_condi')
        ]);
    }
}
