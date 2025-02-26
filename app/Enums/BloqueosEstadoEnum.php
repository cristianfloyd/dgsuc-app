<?php

namespace App\Enums;

enum BloqueosEstadoEnum: string
{
    case PENDIENTE = 'pendiente';
    case IMPORTADO = 'importado';
    case DUPLICADO = 'duplicado';
    case VALIDADO = 'validado';
    case FECHAS_COINCIDENTES = 'fechas_coincidentes';
    case FECHA_SUPERIOR = 'fecha_superior';
    case ERROR_VALIDACION = 'error_validacion';
    case PROCESADO = 'procesado';
    case ERROR_PROCESO = 'error_proceso';
    case LICENCIA_YA_BLOQUEADA = 'licencia_ya_bloqueada';

    public function getLabel(): string
    {
        return match($this) {
            self::PENDIENTE => 'Pendiente',
            self::IMPORTADO => 'Recién Importado',
            self::DUPLICADO => 'Duplicado',
            self::VALIDADO => 'validado',
            self::FECHAS_COINCIDENTES => 'Fechas Coincidentes',
            self::FECHA_SUPERIOR => 'Fecha Superior',
            self::ERROR_VALIDACION => 'Error de Validación',
            self::PROCESADO => 'Procesado',
            self::ERROR_PROCESO => 'Error en Proceso',
            self::LICENCIA_YA_BLOQUEADA => 'Licencia Ya Bloqueada',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::IMPORTADO => 'gray',
            self::DUPLICADO => 'warning',
            self::VALIDADO => 'success',
            self::FECHAS_COINCIDENTES => 'warning',
            self::FECHA_SUPERIOR => 'danger',
            self::ERROR_VALIDACION => 'danger',
            self::PROCESADO => 'info',
            self::ERROR_PROCESO => 'warning',
            self::LICENCIA_YA_BLOQUEADA => 'warning',
        };
    }
}
