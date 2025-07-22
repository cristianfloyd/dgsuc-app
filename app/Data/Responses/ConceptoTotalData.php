<?php

namespace App\Data\Responses;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class ConceptoTotalData extends Data
{
    public function __construct(
        #[MapName('id_liquidacion')]
        public readonly int $idLiquidacion,
        #[MapName('codn_conce')]
        public readonly int $codigoConcepto,
        #[MapName('total_impp')]
        public readonly float $importeTotal,
    ) {
    }

    /**
     * Crea una instancia de ConceptoTotalData desde un array de datos.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            idLiquidacion: $data['id_liquidacion'],
            codigoConcepto: $data['codn_conce'],
            importeTotal: (float) $data['total_impp'],
        );
    }


    /**
     * Convierte el DTO a un array para exportación.
     */
    public function toExportArray(): array
    {
        return [
            'ID Liquidación' => $this->idLiquidacion,
            'Código Concepto' => $this->codigoConcepto,
            'Importe Total' => number_format($this->importeTotal, 2, ',', '.'),
        ];
    }
}
