<?php

namespace App\Data\Responses;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapName;

class SicossTotalesData extends Data
{
    public function __construct(
        #[MapName('total_aportes')]
        public readonly float $totalAportes,

        #[MapName('total_contribuciones')]
        public readonly float $totalContribuciones,

        #[MapName('total_remunerativo')]
        public readonly float $totalRemunerativo,

        #[MapName('total_no_remunerativo')]
        public readonly float $totalNoRemunerativo,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            totalAportes: $data['total_aportes'],
            totalContribuciones: $data['total_contribuciones'],
            totalRemunerativo: $data['total_remunerativo'],
            totalNoRemunerativo: $data['total_no_remunerativo'],
        );
    }

    public function toArray(): array
    {
        return [
            'total_aportes' => $this->totalAportes,
            'total_contribuciones' => $this->totalContribuciones,
            'total_remunerativo' => $this->totalRemunerativo,
            'total_no_remunerativo' => $this->totalNoRemunerativo,
        ];
    }
}
