<?php

namespace App\Data\Responses;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class ConceptoTotalData extends Data
{
    public function __construct(
        #[MapName('id_liquidacion')]
        public readonly int $idLiquidacion,
        
        #[MapName('codn_conce')]
        public readonly int $codigoConcepto,
        
        #[MapName('total_impp')]
        public readonly float $importeTotal,
    ) {}

    /**
     * Crea una instancia de ConceptoTotalData desde un array de datos.
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        return new self(
            idLiquidacion: $data['id_liquidacion'],
            codigoConcepto: $data['codn_conce'],
            importeTotal: (float) $data['total_impp'],
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
            'ID Liquidación' => $this->idLiquidacion,
            'Código Concepto' => $this->codigoConcepto,
            'Importe Total' => number_format($this->importeTotal, 2, ',', '.'),
        ];
    }
} 