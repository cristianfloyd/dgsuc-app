<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AfipArtRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'cuil_formateado' => 'nullable|string|max:13',
            'cuil_original' => 'required|string|max:11',
            'apellido_y_nombre' => 'nullable|string|max:255',
            'nacimiento' => 'nullable|date',
            'sueldo' => 'nullable|string',
            'sexo' => 'nullable|string|size:1',
            'nro_legaj' => 'nullable|integer',
            'establecimiento' => 'nullable|string|max:50',
            'tarea' => 'nullable|string|max:50',
            'concepto' => 'nullable|integer',
        ];
    }
}
