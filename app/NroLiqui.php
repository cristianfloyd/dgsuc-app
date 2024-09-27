<?php

namespace App;

class NroLiqui
{
    private $value;

    public function __construct(int $value)
    {
        // Validación del valor de entrada
        if ($value <= 0) {
            throw new \InvalidArgumentException('El número de liquidación debe ser positivo');
        }
        $this->value = $value;
    }

    // Método getter para obtener el valor
    public function getValue(): int
    {
        return $this->value;
    }

    // Método para comparar dos objetos NroLiqui
    public function equals(NroLiqui $other): bool
    {
        return $this->value === $other->getValue();
    }

    // Método para representación en string
    public function __toString(): string
    {
        return (string) $this->value;
    }
}
