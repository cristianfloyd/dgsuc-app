<?php

namespace App\Enums;

enum BloqueosEstadoEnum: string
{
    case PENDIENTE = 'pendiente';
    case IMPORTADO = 'importado';
    case VALIDADO = 'validado';
    case ERROR_VALIDACION = 'error_validacion';
    case PROCESADO = 'procesado';
    case ERROR_PROCESO = 'error_proceso';

    public function getLabel(): string
    {
        return match($this) {
            self::PENDIENTE => 'Pendiente',
            self::IMPORTADO => 'Recién Importado',
            self::VALIDADO => 'validado',
            self::ERROR_VALIDACION => 'Error de Validación',
            self::PROCESADO => 'Procesado',
            self::ERROR_PROCESO => 'Error en Proceso',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::IMPORTADO => 'gray',
            self::VALIDADO => 'success',
            self::ERROR_VALIDACION => 'danger',
            self::PROCESADO => 'info',
            self::ERROR_PROCESO => 'warning',
        };
    }
}
