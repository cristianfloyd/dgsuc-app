<?php

namespace App\DTOs\Mapuche\Dh13;

use Illuminate\Http\Request;

/**
 * DTO para la actualización de registros Dh13.
 */
class UpdateDh13DTO extends Dh13DTO
{
    /**
     * Crea una instancia desde una Request.
     *
     * @param Request $request Request HTTP
     * @param int $codn_conce Código de concepto existente
     * @param int $nro_orden_formula Número de orden existente
     *
     * @return self Nueva instancia del DTO
     */
    public static function fromRequest(
        Request $request,
        int $codn_conce,
        int $nro_orden_formula,
    ): self {
        return new self(
            codn_conce: $codn_conce,
            desc_calcu: $request->string('desc_calcu'),
            nro_orden_formula: $nro_orden_formula,
            desc_condi: $request->string('desc_condi'),
        );
    }

    /**
     * Obtiene solo los campos modificables para actualización.
     */
    public function getUpdateableFields(): array
    {
        return array_filter([
            'desc_calcu' => $this->desc_calcu,
            'desc_condi' => $this->desc_condi,
        ]);
    }
}
