<?php

namespace App\Data\Responses;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class ConceptoTotalAgrupacionData extends Data
{
    public function __construct(
        #[MapName('haberes')]
        public readonly Collection $haberes,
        #[MapName('descuentos')]
        public readonly Collection $descuentos,
        #[MapName('total_haberes')]
        public readonly float $totalHaberes,
        #[MapName('total_descuentos')]
        public readonly float $totalDescuentos,
        #[MapName('neto')]
        public readonly float $neto,
    ) {
    }

    /**
     * Crea una instancia desde el resultado del repositorio.
     *
     *
     */
    public static function fromRepositoryResult(array $data): static
    {
        return new static(
            haberes: $data['haberes'],
            descuentos: $data['descuentos'],
            totalHaberes: (float) $data['total_haberes'],
            totalDescuentos: (float) $data['total_descuentos'],
            neto: (float) $data['neto'],
        );
    }

    /**
     * Convierte el DTO a un array para exportación.
     */
    public function toExportArray(): array
    {
        $haberesArray = $this->haberes->map(fn($item): array => [
            'Código' => $item->codn_conce,
            'Concepto' => $item->desc_conce,
            'Importe' => number_format($item->importe, 2, ',', '.'),
        ])->toArray();

        $descuentosArray = $this->descuentos->map(fn($item): array => [
            'Código' => $item->codn_conce,
            'Concepto' => $item->desc_conce,
            'Importe' => number_format($item->importe, 2, ',', '.'),
        ])->toArray();

        return [
            'Haberes' => $haberesArray,
            'Descuentos' => $descuentosArray,
            'Total Haberes' => number_format($this->totalHaberes, 2, ',', '.'),
            'Total Descuentos' => number_format($this->totalDescuentos, 2, ',', '.'),
            'Neto' => number_format($this->neto, 2, ',', '.'),
        ];
    }
}
