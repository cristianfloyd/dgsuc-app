<?php

namespace App\Data\Responses;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

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
        #[MapName('total_c305')]
        public readonly float $totalC305,
        #[MapName('total_c306')]
        public readonly float $totalC306,
    ) {
    }

    /**
     * Summary of fromArray
     * @param array $data
     * @return SicossTotalesData
     */
    public static function fromArray(array $data): self
    {
        return new self(
            totalAportes: $data['total_aportes'],
            totalContribuciones: $data['total_contribuciones'],
            totalRemunerativo: $data['total_remunerativo'],
            totalNoRemunerativo: $data['total_no_remunerativo'],
            totalC305: $data['total_c305'],
            totalC306: $data['total_c306'],
        );
    }

    /**
     * Convertir el objeto de datos a un array.
     *
     * @return array<string, float> La representacion del objeto de datos en forma de array.
     */
    public function toArray(): array
    {
        return [
            'total_aportes' => $this->totalAportes,
            'total_contribuciones' => $this->totalContribuciones,
            'total_remunerativo' => $this->totalRemunerativo,
            'total_no_remunerativo' => $this->totalNoRemunerativo,
            'total_c305' => $this->totalC305,
            'total_c306' => $this->totalC306,
        ];
    }

    /**
     * Obtiene los valores por defecto para los totales.
     */
    public static function getDefaultValues(): array
    {
        return [
            'total_aportes' => 0,
            'total_contribuciones' => 0,
            'total_remunerativo' => 0,
            'total_no_remunerativo' => 0,
            'total_c305' => 0,
            'total_c306' => 0,
        ];
    }
}
