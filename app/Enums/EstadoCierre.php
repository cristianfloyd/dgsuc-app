<?php

namespace App\Enums;

enum EstadoCierre: string
{
    case CERRADO = 'S';

    case ABIERTO = 'N';

    public function getLabel(): string
    {
        return match ($this) {
            self::CERRADO => 'Cerrado',
            self::ABIERTO => 'Abierto',
        };
    }

    
    /**
     * Returns an array suitable for use in a select element.
     *
     * @return array<string, string>
     */
    public static function asSelectArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }
}
