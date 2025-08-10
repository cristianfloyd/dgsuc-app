<?php

namespace App\Http\Requests\Mapuche;

use Illuminate\Foundation\Http\FormRequest;

class Dh13Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codn_conce' => 'required|integer|exists:mapuche.dh12,codn_conce',
            'desc_calcu' => 'nullable|string|max:250',
            'nro_orden_formula' => 'required|integer',
            'desc_condi' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'codn_conce.required' => 'El código de concepto es obligatorio',
            'codn_conce.exists' => 'El código de concepto no existe',
            'nro_orden_formula.required' => 'El número de orden es obligatorio',
        ];
    }
}
