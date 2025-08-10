<?php

namespace App\Enums;

enum EstadoLaboral: string
{
    case PLANTA = 'P';

    case CONTRATO = 'C';

    case AD_HONOREM = 'A';

    case BECARIO = 'B';

    case SERVICIOS = 'S';

    case OBRA = 'O';

    public function descripcion(): string
    {
        return match ($this) {
            self::PLANTA => 'Planta',
            self::CONTRATO => 'Contrato',
            self::AD_HONOREM => 'Ad Honorem',
            self::BECARIO => 'Becario',
            self::SERVICIOS => 'Cont. Loc. Servicios',
            self::OBRA => 'Cont. Loc. Obra',
        };
    }

    /**
     * Devuelve un array asociativo donde las claves son los valores del enum
     * y los valores son las descripciones.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($estado) => [
            $estado->value => $estado->descripcion(),
        ])->toArray();
    }
}
