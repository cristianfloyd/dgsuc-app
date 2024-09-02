<?php

namespace App\ValueObjects;

use InvalidArgumentException;

class TipoRetro
{
    private int $value;

    public function __construct(int $tipoRetro)
    {
        if ($tipoRetro < 1 || $tipoRetro > 5) {
            throw new InvalidArgumentException('El tipo de retro debe ser un número entre 1 y 5.');
        }
        $this->value = $tipoRetro;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
