<?php

namespace App\Enums;

enum SicossCodigoActividad: int
{
    case DOCENTE_INVESTIGADOR = 37;

    case DOCENTE_UNIVERSITARIO = 35;

    case DOCENTE_ESPECIAL = 88;

    case DOCENTE_ADMINISTRATIVO = 17;

    public function descripcion(): string
    {
        return match ($this) {
            self::DOCENTE_INVESTIGADOR => 'Docente Universitario',
            self::DOCENTE_UNIVERSITARIO => 'Docente Terciario',
            self::DOCENTE_ESPECIAL => 'Docente Especial',
            self::DOCENTE_ADMINISTRATIVO => 'Docente Administrativo',
        };
    }

    public static function fromCodact(int $codact): ?self
    {
        return match ($codact) {
            1 => self::DOCENTE_INVESTIGADOR,
            2 => self::DOCENTE_UNIVERSITARIO,
            3 => self::DOCENTE_ESPECIAL,
            4 => self::DOCENTE_ADMINISTRATIVO,
            default => null
        };
    }
}
