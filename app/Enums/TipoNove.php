<?php

namespace App\Enums;

enum TipoNove: string
{
/**
     * General: El concepto no necesita una novedad para liquidarse,
     * sólo puede forzarse o anularse.
     */
    case GENERAL = 'G';

/**
     * Permanente: El concepto necesita una novedad para ser liquidado.
     * Puede incluir valores que afectan la fórmula (Importe, Porcentaje, Cantidad)
     */
    case PERMANENTE = 'P';

/**
     * Transitorio: Necesita novedad para ser liquidado.
     * Puede ser forzado o anulado por un período de tiempo (ej: Horas Extras)
     */
    case TRANSITORIO = 'T';

    public function getDescription(): string
    {
        return match ($this) {
            self::GENERAL => 'No necesita novedad para liquidarse, solo puede forzarse o anularse',
            self::PERMANENTE => 'Necesita novedad para ser liquidado. Puede incluir valores que afectan la fórmula',
            self::TRANSITORIO => 'Necesita novedad para ser liquidado. Puede ser forzado o anulado por período',
        };
    }

    public function getTiposValores(): array
    {
        return match ($this) {
            self::GENERAL => [],
            self::PERMANENTE, self::TRANSITORIO => ['Importe', 'Porcentaje', 'Cantidad'],
        };
    }
}
