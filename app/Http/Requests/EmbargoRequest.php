<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmbargoRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true; // Ajustar según la lógica de autorización del proyecto
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     */
    public function rules(): array
    {
        return [
            'nroComplementarias' => ['present', 'array'],
            'nroComplementarias.*' => ['integer', 'min:1'],
            'nroLiquiDefinitiva' => ['required', 'integer', 'min:1'],
            'nroLiquiProxima' => ['required', 'integer', 'min:1', 'gte:nroLiquiDefinitiva'],
            'insertIntoDh25' => ['boolean'],
        ];
    }

    /**
     * Obtiene los mensajes de error personalizados para las reglas de validación.
     */
    public function messages(): array
    {
        return [
            'nroComplementarias.present' => 'El campo números complementarias debe estar presente',
            'nroComplementarias.array' => 'Los números complementarias deben ser un arreglo',
            'nroComplementarias.*.integer' => 'Los valores de números complementarias deben ser números enteros',
            'nroComplementarias.*.min' => 'Los valores de números complementarias deben ser mayores a 0',

            'nroLiquiDefinitiva.required' => 'El número de liquidación definitiva es requerido',
            'nroLiquiDefinitiva.integer' => 'El número de liquidación definitiva debe ser un número entero',
            'nroLiquiDefinitiva.min' => 'El número de liquidación definitiva debe ser mayor a 0',

            'nroLiquiProxima.required' => 'El número de liquidación próxima es requerido',
            'nroLiquiProxima.integer' => 'El número de liquidación próxima debe ser un número entero',
            'nroLiquiProxima.min' => 'El número de liquidación próxima debe ser mayor a 0',
            'nroLiquiProxima.gte' => 'El número de liquidación próxima debe ser mayor o igual al número de liquidación definitiva',

            'insertIntoDh25.boolean' => 'El campo insertar en DH25 debe ser verdadero o falso',
        ];
    }

    /**
     * Prepara los datos para la validación.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'insertIntoDh25' => $this->boolean('insertIntoDh25'),
            'nroComplementarias' => $this->nroComplementarias ?? [],
        ]);
    }
}
