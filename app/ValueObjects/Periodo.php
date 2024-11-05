<?php

namespace App\ValueObjetcs;

use InvalidArgumentException;


/**
 * Representa un periodo de tiempo en formato YYYYMM.
 *
 * Esta clase encapsula la validación y el acceso al valor del periodo.
 */
class Periodo
{
    private string $value;

    public function __construct(string $periodo)
    {
        if (!preg_match('/^\d{6}$/', $periodo)) {
            throw new InvalidArgumentException('El periodo debe ser una cadena de 6 dígitos.');
        }
        $this->value = $periodo;
    }

    /// Obtiene el valor del periodo como una cadena de texto.
    public function getValue(): string
    {
        return $this->value;
    }

    /// Devuelve el valor del periodo como una cadena de texto.
    public function __toString(): string
    {
        return $this->value;
    }
}
