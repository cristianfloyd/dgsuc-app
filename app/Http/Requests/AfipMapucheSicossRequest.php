<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AfipMapucheSicossRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'periodo_fiscal' => 'required|string|size:6',
            'cuil' => "required|string|size:11|unique:suc.afip_mapuche_sicoss,cuil,{$this->cuil},cuil,periodo_fiscal,{$this->periodo_fiscal}",
            'apnom' => 'nullable|string|max:30',
            'conyuge' => 'nullable|string|size:1',
            'cant_hijos' => 'nullable|string|size:2',
            // Agrega aquí las reglas para los demás campos
        ];
    }

    /**
     * Obtiene los mensajes de error personalizados para las reglas de validación.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'periodo_fiscal.required' => 'El período fiscal es obligatorio.',
            'periodo_fiscal.size' => 'El período fiscal debe tener 6 caracteres.',
            'cuil.required' => 'El CUIL es obligatorio.',
            'cuil.size' => 'El CUIL debe tener 11 caracteres.',
            'cuil.unique' => 'Ya existe un registro con este CUIL para el período fiscal especificado.',
            // Agrega aquí los mensajes para las demás reglas
        ];
    }
}
