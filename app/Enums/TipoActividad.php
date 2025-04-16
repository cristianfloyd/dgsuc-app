<?php

namespace App\Enums;

enum TipoActividad: string
{
    case UNIVERSITARIA = '853201';
    case TERCIARIA = '803200';
    case INICIAL = '851110';
    case SECUNDARIA = '852100';
    case SECUNDARIAL = '852200';
    case INTERNACION = '861010';
    case PRIMARIA = '802100';
    case ADMINISTRATIVA = '751100';

    public function descripcion(): string
    {
        return match ($this) {
            self::UNIVERSITARIA => 'Enseñanza universitaria de grado',
            self::TERCIARIA => 'Enseñanza terciaria',
            self::INICIAL => 'Enseñanza inicial',
            self::INTERNACION => 'Servicios de internación',
            self::SECUNDARIA => 'Enseñanza secundaria',
            self::SECUNDARIAL => 'Enseñanza secundaria Lugano',
            self::PRIMARIA => 'Enseñanza primaria',
            self::ADMINISTRATIVA => 'Servicios administrativos',
        };
    }
}
