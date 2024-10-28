<?php

namespace App\ValueObjects;

class PeriodoFiscal
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly int $año,
        private readonly int $mes,
    ){}

    public function año(): int
    {
        return $this->año;
    }

    public function mes(): int
    {
        return $this->mes;
    }

    public function toString(): string
    {
        return $this->año . str_pad($this->mes, 2, '0', STR_PAD_LEFT);
    }
}
