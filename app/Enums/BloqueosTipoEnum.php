<?php

namespace App\Enums;

enum BloqueosTipoEnum: string
{
    case LICENCIA = 'licencia';

    case FALLECIDO = 'fallecido';

    case RENUNCIA = 'renuncia';
    // Agrega aquí otros tipos si existen

    public function getLabel(): string
    {
        return match ($this) {
            self::LICENCIA => 'Licencia',
            self::FALLECIDO => 'Fallecido',
            self::RENUNCIA => 'Renuncia',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::LICENCIA => 'info',
            self::FALLECIDO => 'danger',
            self::RENUNCIA => 'warning',
        };
    }
}
