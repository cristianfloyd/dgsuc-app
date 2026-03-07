<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;


trait HasFixedWithImportes
{

  /**
   * Genera un Attribute para obtener y establecer un valor de importe con formato de ancho fijo.
   *
   * @param string $field El nombre del campo a procesar
   * @return Attribute Un atributo de Eloquent con lógica de formateo para importes
   */
  protected function fixedWidthImporte(string $field): Attribute
  {
    return Attribute::make(
      get: function ($value, $attributes) use ($field) {
        $clean = trim($attributes[$field] ?? '');

        if ($clean === '' || !is_numeric($clean)) {
          return null;
        }

        return (float)$clean;
      },
      set: function ($value) use ($field) {
        return [
          $field => str_pad(
            number_format(
              num: (float)$value,
              decimals: 2,
              decimal_separator: '.',
              thousands_separator: ''
            ),
            length: 12,
            pad_string: ' ',
            pad_type: STR_PAD_LEFT
          ),
        ];
      }
    );
  }

  
  /**
   * Genera un Attribute para obtener un valor de importe con formato de ancho fijo.
   *
   * @param string $field El nombre del campo a procesar
   * @return Attribute Un atributo de Eloquent con lógica de formateo para importes de ancho fijo
   */
  protected function fixedWidthImporteFixed(string $field): Attribute
  {
    return Attribute::make(
      get: fn ($value, $attributes) =>
        str_pad(rtrim($attributes[$field] ?? ''), 12, ' ', STR_PAD_LEFT)
    );
  }
}