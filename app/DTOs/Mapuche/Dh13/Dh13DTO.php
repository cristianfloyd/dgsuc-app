<?php

namespace App\DTOs\Mapuche\Dh13;

use Spatie\LaravelData\Data;

/**
 * DTO base para la entidad Dh13.
 *
 * @property int $codn_conce Código de concepto
 * @property string|null $desc_calcu Descripción del cálculo
 * @property int $nro_orden_formula Número de orden de la fórmula
 * @property string|null $desc_condi Descripción de la condición
 */
class Dh13DTO extends Data
{
    /**
     * Constructor del DTO.
     */
    public function __construct(
        public readonly int $codn_conce,
        public readonly ?string $desc_calcu,
        public readonly int $nro_orden_formula,
        public readonly ?string $desc_condi,
    ) {
    }

    /**
     * Convierte el DTO a un array para persistencia.
     */
    public function toArray(): array
    {
        return [
            'codn_conce' => $this->codn_conce,
            'desc_calcu' => $this->desc_calcu,
            'nro_orden_formula' => $this->nro_orden_formula,
            'desc_condi' => $this->desc_condi,
        ];
    }
}
