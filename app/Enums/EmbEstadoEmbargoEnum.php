<?php

declare(strict_types=1);

namespace App\Enums;

enum EmbEstadoEmbargoEnum: int
{
    case ACTIVO = 1;

    case FINALIZADO = 2;

    case SUSPENDIDO = 3;

    case EN_PROCESO = 4;

    case CANCELADO = 5;

    public function descripcion(): string
    {
        return match ($this) {
            self::ACTIVO => 'Activo',
            self::FINALIZADO => 'Finalizado',
            self::SUSPENDIDO => 'Suspendido',
            self::EN_PROCESO => 'En proceso',
            self::CANCELADO => 'Cancelado',
        };
    }
}
