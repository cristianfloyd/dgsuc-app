<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Dhe2Request extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Ajusta esto según tus necesidades de autorización
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'nro_tabla' => 'required|integer',
            'desc_abrev' => 'required|string|max:4',
            'cod_organismo' => 'nullable|integer|exists:mapuche.dhe4,cod_organismo',
        ];
    }

    /**
     * Obtiene los mensajes de error personalizados para las reglas de validación.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'nro_tabla.required' => 'El número de tabla es obligatorio.',
            'nro_tabla.integer' => 'El número de tabla debe ser un número entero.',
            'desc_abrev.required' => 'La descripción abreviada es obligatoria.',
            'desc_abrev.string' => 'La descripción abreviada debe ser una cadena de texto.',
            'desc_abrev.max' => 'La descripción abreviada no puede tener más de 4 caracteres.',
            'cod_organismo.integer' => 'El código de organismo debe ser un número entero.',
            'cod_organismo.exists' => 'El código de organismo especificado no existe.',
        ];
    }
}
