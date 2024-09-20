<?php

namespace App\Http\Requests\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Dh19Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    /**
     * Reglas de validación para la solicitud Dh19.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'nro_legaj' => 'required|integer',
            'codn_conce' => 'required|integer|exists:mapuche.dh12,codn_conce',
            'nro_tabla' => 'nullable|integer',
            'tipo_docum' => 'required|string|size:4',
            'nro_docum' => 'required|integer',
            'desc_apell' => 'nullable|string|max:30',
            'desc_nombre' => 'nullable|string|max:30',
            'porc_benef' => 'nullable|numeric|between:0,100',
        ];
    }
}
