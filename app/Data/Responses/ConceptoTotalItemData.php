<?php

namespace App\Data\Responses;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class ConceptoTotalItemData extends Data
{
    public function __construct(
        #[MapName('codn_conce')]
        public readonly string $codigoConcepto,
        #[MapName('desc_conce')]
        public readonly string $descripcionConcepto,
        #[MapName('importe')]
        public readonly float $importe,
    ) {
    }

    /**
     * Crea una instancia desde los datos de BD.
     *
     * @param object $rowData
     *
     * @return static
     */
    public static function fromRowData(object $rowData): static
    {
        return new static(
            codigoConcepto: $rowData->codn_conce,
            descripcionConcepto: $rowData->desc_conce,
            importe: (float)$rowData->importe,
        );
    }

    /**
     * Convierte el DTO a un array para exportación.
     *
     * @return array
     */
    public function toExportArray(): array
    {
        return [
            'Código' => $this->codigoConcepto,
            'Concepto' => $this->descripcionConcepto,
            'Importe' => number_format($this->importe, 2, ',', '.'),
        ];
    }
}
