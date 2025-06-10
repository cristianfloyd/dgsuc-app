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

    public static function asSelectArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }
}
