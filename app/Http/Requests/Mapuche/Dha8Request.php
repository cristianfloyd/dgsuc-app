<?php

namespace App\Http\Requests\Mapuche;

use Illuminate\Foundation\Http\FormRequest;

class Dha8Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'codigosituacion' => 'nullable|integer',
            'codigocondicion' => 'nullable|integer',
            'codigoactividad' => 'nullable|integer',
            'codigozona' => 'nullable|integer',
            'porcaporteadicss' => 'nullable|numeric|between:0,100',
            'codigomodalcontrat' => 'nullable|integer',
            'provincialocalidad' => 'nullable|string|max:50',
        ];
    }
}
