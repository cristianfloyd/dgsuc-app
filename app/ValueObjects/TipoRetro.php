<?php

namespace App\ValueObjects;

use InvalidArgumentException;

class TipoRetro
{

    public function __construct(protected int $value)
    {
        if ($value < 1 || $value > 5) {
            throw new InvalidArgumentException('El tipo de retro debe ser un número entre 1 y 5.');
        }
        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
