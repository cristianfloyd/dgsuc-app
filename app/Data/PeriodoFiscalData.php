<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\IntegerType;

class PeriodoFiscalData extends Data
{
    public function __construct(
        #[IntegerType, Min(2000), Max(2100)]
        public readonly int $year,
        
        #[IntegerType, Min(1), Max(12)]
        public readonly int $month,
    ) {
    }

    /**
     * Obtiene el período fiscal formateado como YYYY-MM
     */
    public function getFormattedPeriod(): string
    {
        return sprintf('%d-%02d', $this->year, $this->month);
    }

    /**
     * Crea una instancia del período fiscal actual
     */
    public static function current(): self
    {
        $now = now();
        return new self($now->year, $now->month);
    }

    /**
     * Crea una instancia a partir de un array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['year'] ?? now()->year,
            $data['month'] ?? now()->month
        );
    }
}