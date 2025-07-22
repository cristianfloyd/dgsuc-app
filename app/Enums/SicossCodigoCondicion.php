<?php

namespace App\Enums;

enum SicossCodigoCondicion: int
{
    case JUBILADO_DECRETO_894 = 0;

    case SERVICIOS_COMUNES_MAYOR_18 = 1;

    case JUBILADO = 2;

    case MENOR = 3;

    case MENOR_ANTERIOR = 4;

    case SERVICIOS_DIFERENCIADOS_MAYOR_18 = 5;

    case PRE_JUBILABLES = 6;

    case MEDIDA_NO_INNOVAR_SERV_COMUNES = 7;

    case MEDIDA_NO_INNOVAR_SERV_DIFERENCIADOS = 8;

    case JUBILADO_DECRETO_206 = 9;

    case PENSION_NO_SIPA = 10;

    case PENSION_NO_CONTRIBUTIVA = 11;

    case ART_8_LEY_27426 = 12;

    case SERVICIOS_DIFERENCIADOS_NO_ALCANZADOS = 13;

    case JUBILADO_DOCENTES_UNIVERSITARIOS = 14;

    public function descripcion(): string
    {
        return match($this) {
            self::JUBILADO_DECRETO_894 => 'Jubilado Decreto N° 894/01 y/o Dec 2288/02',
            self::SERVICIOS_COMUNES_MAYOR_18 => 'SERVICIOS COMUNES Mayor de 18 años',
            self::JUBILADO => 'Jubilado',
            self::MENOR => 'Menor',
            self::MENOR_ANTERIOR => 'Menor Anterior',
            self::SERVICIOS_DIFERENCIADOS_MAYOR_18 => 'SERVICIOS DIFERENCIADOS Mayor de 18 años',
            self::PRE_JUBILABLES => 'Pre- jubilables Sin relacion de dependencia -Sin servicios reales',
            self::MEDIDA_NO_INNOVAR_SERV_COMUNES => 'MEDIDA DE NO INNOVAR SERV. COMUNES',
            self::MEDIDA_NO_INNOVAR_SERV_DIFERENCIADOS => 'MEDIDA DE NO INNOVAR SERV. DIFERENCIAD',
            self::JUBILADO_DECRETO_206 => 'Jubilado Decreto N° 206/00 y/o Decreto Nº 894/01',
            self::PENSION_NO_SIPA => 'Pensión (NO SIPA)',
            self::PENSION_NO_CONTRIBUTIVA => 'Pensión no Contributiva (NO SIPA)',
            self::ART_8_LEY_27426 => 'Art. 8º Ley Nº 27426',
            self::SERVICIOS_DIFERENCIADOS_NO_ALCANZADOS => 'Servicios Diferenciados no alcanzados por el Dto. 633/2018',
            self::JUBILADO_DOCENTES_UNIVERSITARIOS => 'Jubilado - Docentes universitarios. Docentes e investigadores científ./ tecnológ.',
        };
    }

    /**
     * Obtiene el enum a partir de un código numérico.
     */
    public static function fromCodigo(int $codigo): ?self
    {
        return match($codigo) {
            0 => self::JUBILADO_DECRETO_894,
            1 => self::SERVICIOS_COMUNES_MAYOR_18,
            2 => self::JUBILADO,
            3 => self::MENOR,
            4 => self::MENOR_ANTERIOR,
            5 => self::SERVICIOS_DIFERENCIADOS_MAYOR_18,
            6 => self::PRE_JUBILABLES,
            7 => self::MEDIDA_NO_INNOVAR_SERV_COMUNES,
            8 => self::MEDIDA_NO_INNOVAR_SERV_DIFERENCIADOS,
            9 => self::JUBILADO_DECRETO_206,
            10 => self::PENSION_NO_SIPA,
            11 => self::PENSION_NO_CONTRIBUTIVA,
            12 => self::ART_8_LEY_27426,
            13 => self::SERVICIOS_DIFERENCIADOS_NO_ALCANZADOS,
            14 => self::JUBILADO_DOCENTES_UNIVERSITARIOS,
            default => null
        };
    }
}
