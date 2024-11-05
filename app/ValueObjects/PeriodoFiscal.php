<?php

namespace App\ValueObjects;

readonly class PeriodoFiscal
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private int $anio,
        private int $mes,
    )
    {
    }

    public function anio(): int
    {
        return $this->anio;
    }

    public function mes(): int
    {
        return $this->mes;
    }

    public function toString(): string
    {
        return $this->anio . str_pad($this->mes, 2, '0', STR_PAD_LEFT);
    }
}
