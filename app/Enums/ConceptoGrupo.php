<?php

namespace App\Enums;

enum ConceptoGrupo: string
{
    case DOSUBA = 'dosuba';
    case AFIP = 'afip';
    case APORTES_Y_CONTRIBUCIONES = 'aportes_y_contribuciones';
    case CONTRIBUCIONES_AFIP = 'contribuciones_afip';
    case SEGURO_CONTRIBUCION_AFIP = 'seguro_contribucion_afip';
    case ART_AFIP = 'art_afip';
    case APORTES_AFIP = 'aportes_afip';

    /**
     * Obtiene los códigos de conceptos asociados a cada grupo
     */
    public function getConceptos(): array
    {
        return match($this) {
            self::DOSUBA => [
                // Rango de conceptos DOSUBA
                207, 210, 211, 213, 214, 246, 252, 283, 285, 286, 287, 310
            ],
            self::CONTRIBUCIONES_AFIP => [
                // Rango de conceptos AFIP
                301, 302, 303, 304, 307, 308, 347, 348
            ],
            self::SEGURO_CONTRIBUCION_AFIP => [
                305,
            ],
            self::ART_AFIP => [
                306,
            ],
            self::APORTES_AFIP => [
                201, 202, 203, 204, 205, 247, 248,
            ],
            self::AFIP => [
                ...self::CONTRIBUCIONES_AFIP->getConceptos(),
                ...self::SEGURO_CONTRIBUCION_AFIP->getConceptos(),
                ...self::ART_AFIP->getConceptos(),
                ...self::APORTES_AFIP->getConceptos(),
            ],
            self::APORTES_Y_CONTRIBUCIONES => [
                ...self::DOSUBA->getConceptos(),
                ...self::AFIP->getConceptos()
            ]
        };
    }

    /**
     * Verifica si un concepto pertenece al grupo
     */
    public function containsConcepto(int $codn_conce): bool
    {
        return in_array($codn_conce, $this->getConceptos());
    }

    /**
     * Obtiene la descripción del grupo
     */
    public function getDescripcion(): string
    {
        return match($this) {
            self::DOSUBA => 'Conceptos DOSUBA',
            self::AFIP => 'Conceptos AFIP',
            self::APORTES_Y_CONTRIBUCIONES => 'Aportes y Contribuciones'
        };
    }
}
