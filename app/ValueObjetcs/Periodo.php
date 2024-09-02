<?php

namespace App\ValueObjects;

use InvalidArgumentException;

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

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
