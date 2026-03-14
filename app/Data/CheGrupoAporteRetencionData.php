<?php

declare(strict_types=1);

namespace App\Data;

use Override;
use Spatie\LaravelData\Data;

/**
 * DTO para un ítem de aportes/retenciones del archivo CHE.
 */
class CheGrupoAporteRetencionData extends Data
{
    public function __construct(
        public readonly string $codigo,
        public readonly string $descripcion,
        public readonly string $importe,
    ) {}

    /**
     * Crea una instancia desde un array de aporte (grupo, desc_grupo, total).
     */
    public static function fromAporteArray(
        array $aporte,
        callable $fillWithZeros,
        callable $fillWithSpaces,
        callable $fillWithLeftSpaces,
    ): self {
        return new self(
            codigo: $fillWithZeros($aporte['grupo'], 3),
            descripcion: $fillWithSpaces($aporte['desc_grupo'], 50),
            importe: $fillWithLeftSpaces(number_format((float) $aporte['total'], 2, '.', ''), 16),
        );
    }

    #[Override]
    public function toArray(): array
    {
        return [
            'codigo' => $this->codigo,
            'descripcion' => $this->descripcion,
            'importe' => $this->importe,
        ];
    }
}
