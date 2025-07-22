<?php

namespace App\Http\Requests\Mapuche;

use Illuminate\Foundation\Http\FormRequest;

class Dh11Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para la solicitud Dh11.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'codc_categ' => 'required|string|size:4',
            'equivalencia' => 'nullable|string|size:3',
            'tipo_escal' => 'nullable|string|size:1',
            'nro_escal' => 'nullable|integer',
            'impp_basic' => 'nullable|numeric|between:0,9999999.99',
            'codc_dedic' => 'nullable|string|size:4|exists:dh31,codc_dedic',
            'sino_mensu' => 'nullable|string|size:1',
            'sino_djpat' => 'nullable|string|size:1',
            'vig_caano' => 'nullable|integer',
            'vig_cames' => 'nullable|integer',
            'desc_categ' => 'nullable|string|max:20',
            'sino_jefat' => 'nullable|string|size:1',
            'impp_asign' => 'nullable|numeric|between:0,9999999.99',
            'computaantig' => 'nullable|integer',
            'controlcargos' => 'nullable|boolean',
            'controlhoras' => 'nullable|boolean',
            'controlpuntos' => 'nullable|boolean',
            'controlpresup' => 'nullable|boolean',
            'horasmenanual' => 'nullable|string|size:1',
            'cantpuntos' => 'nullable|integer',
            'estadolaboral' => 'nullable|string|size:1',
            'nivel' => 'nullable|string|size:3',
            'tipocargo' => 'nullable|string|max:30',
            'remunbonif' => 'nullable|numeric',
            'noremunbonif' => 'nullable|numeric',
            'remunnobonif' => 'nullable|numeric',
            'noremunnobonif' => 'nullable|numeric',
            'otrasrem' => 'nullable|numeric',
            'dto1610' => 'nullable|numeric',
            'reflaboral' => 'nullable|numeric',
            'refadm95' => 'nullable|numeric',
            'critico' => 'nullable|numeric',
            'jefatura' => 'nullable|numeric',
            'gastosrepre' => 'nullable|numeric',
            'codigoescalafon' => 'nullable|string|size:4|exists:dh89,codigoescalafon',
            'noinformasipuver' => 'nullable|integer',
            'noinformasirhu' => 'nullable|integer',
            'imppnooblig' => 'nullable|integer',
            'aportalao' => 'nullable|boolean',
            'factor_hs_catedra' => 'nullable|numeric',
        ];
    }

    /**
     * Mensajes personalizados para las reglas de validación.
     */
    public function messages(): array
    {
        return [
            'codc_categ.required' => 'El código de categoría es obligatorio',
            'codc_categ.size' => 'El código de categoría debe tener exactamente 4 caracteres',
            'equivalencia.size' => 'La equivalencia debe tener exactamente 3 caracteres',
            'tipo_escal.size' => 'El tipo de escalafón debe tener exactamente 1 caracter',
            'impp_basic.between' => 'El importe básico debe estar entre 0 y 9.999.999,99',
            'codc_dedic.exists' => 'El código de dedicación no existe en el sistema',
            'sino_mensu.in' => 'El campo mensualizado solo acepta valores S o N',
            'sino_djpat.in' => 'El campo declaración jurada solo acepta valores S o N',
            'vig_cames.between' => 'El mes debe estar entre 1 y 12',
            'desc_categ.max' => 'La descripción no puede superar los 20 caracteres',
            'sino_jefat.in' => 'El campo jefatura solo acepta valores S o N',
            'horasmenanual.in' => 'El campo horas solo acepta valores M o A',
            'estadolaboral.in' => 'El estado laboral solo acepta valores P,C,A,B,S,O',
            'tipocargo.max' => 'El tipo de cargo no puede superar los 30 caracteres',
            'codigoescalafon.exists' => 'El código de escalafón no existe en el sistema',
        ];
    }

    /**
     * Prepara los datos para la validación.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'sino_mensu' => strtoupper($this->sino_mensu),
            'sino_djpat' => strtoupper($this->sino_djpat),
            'sino_jefat' => strtoupper($this->sino_jefat),
            'horasmenanual' => strtoupper($this->horasmenanual),
            'estadolaboral' => strtoupper($this->estadolaboral),
        ]);
    }
}
