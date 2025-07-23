<?php

namespace App\ValueObjects;

/**
 * Representa un periodo de tiempo en formato YYYYMM.
 *
 * Esta clase encapsula la validación y el acceso al valor del periodo.
 */
class Periodo implements \Stringable
{
    private readonly string $value;

    public function __construct(string $periodo)
    {
        if (!preg_match('/^\d{6}$/', $periodo)) {
            throw new \InvalidArgumentException('El periodo debe ser una cadena de 6 dígitos.');
        }
        $this->value = $periodo;
    }

    /// Devuelve el valor del periodo como una cadena de texto.
    public function __toString(): string
    {
        return $this->value;
    }

    /// Obtiene el valor del periodo como una cadena de texto.
    public function getValue(): string
    {
        return $this->value;
    }
}
