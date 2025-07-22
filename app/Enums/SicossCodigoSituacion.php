<?php

namespace App\Enums;

enum SicossCodigoSituacion: int
{
    case BAJA_FALLECIMIENTO = 0;

    case ACTIVO = 1;

    case BAJA_OTRAS_CAUSALES = 2;

    case ACTIVO_DECRETO_796 = 3;

    case BAJA_DECRETO_796 = 4;

    case MATERNIDAD = 5;

    case SUSPENDIDO = 6;

    case BAJA_DESPIDO = 7;

    case BAJA_DESPIDO_DECRETO_796 = 8;

    case SUSPENDIDO_LEY_20744 = 9;

    case LICENCIA_EXCEDENCIA = 10;

    case LICENCIA_MATERNIDAD_DOWN = 11;

    case LICENCIA_VACACIONES = 12;

    case LICENCIA_SIN_GOCE = 13;

    case RESERVA_PUESTO = 14;

    case ESE_CESE_TRANSITORIO = 15;

    case PERSONAL_SINIESTRADO_ART = 16;

    case REINGRESO_JUDICIAL = 17;

    case ILT_PRIMEROS_10_DIAS = 18;

    case ILT_DIAS_SIGUIENTES = 19;

    case TRABAJADOR_SINIESTRADO_ART = 20;

    case TRABAJADOR_TEMPORADA = 21;

    case ACTIVO_EXTERIOR = 31;

    case LICENCIA_PATERNIDAD = 32;

    case LICENCIA_FUERZA_MAYOR = 33;

    case EMPLEADO_EVENTUAL_MES_COMPLETO = 42;

    case EMPLEADO_EVENTUAL_MES_INCOMPLETO = 43;

    case CONSERVACION_EMPLEO_ART_211 = 44;

    case SUSPENSION_DISCIPLINARIA = 45;

    case SUSPENDIDO_RES_397 = 48;

    case SUSPENSION_PARCIAL = 49;

    case DECRETO_792_GRUPO_RIESGO = 50;

    case LICENCIA_LEY_27674 = 51;

    public function descripcion(): string
    {
        return match($this) {
            self::BAJA_FALLECIMIENTO => 'Baja por Fallecimiento',
            self::ACTIVO => 'Activo',
            self::BAJA_OTRAS_CAUSALES => 'Bajas otras causales',
            self::ACTIVO_DECRETO_796 => 'Activo Decreto N°796/97',
            self::BAJA_DECRETO_796 => 'Baja otras causales Decreto N° 796/97',
            self::MATERNIDAD => 'Maternidad-excedencia-goce de vacaciones y otros',
            self::SUSPENDIDO => 'Suspendido-Conserv del empleo-Lic s/goce/Sdo y otros',
            self::BAJA_DESPIDO => 'Baja por despido',
            self::BAJA_DESPIDO_DECRETO_796 => 'Baja por despido Decreto N° 796/97',
            self::SUSPENDIDO_LEY_20744 => 'Suspendido. Ley 20744 art.223bis',
            self::LICENCIA_EXCEDENCIA => 'Licencia por excedencia',
            self::LICENCIA_MATERNIDAD_DOWN => 'Licencia por maternidad Down',
            self::LICENCIA_VACACIONES => 'Licencia por vacaciones',
            self::LICENCIA_SIN_GOCE => 'Licencia sin goce de haberes',
            self::RESERVA_PUESTO => 'Reserva de puesto',
            self::ESE_CESE_TRANSITORIO => 'E.S.E. Cese transitorio de servicios (art. 6, incs. 6 y 7 Dto. 342/92)',
            self::PERSONAL_SINIESTRADO_ART => 'Personal Siniestrado de terceros uso por la ART',
            self::REINGRESO_JUDICIAL => 'Reingreso por disposición judicial',
            self::ILT_PRIMEROS_10_DIAS => 'ILT primeros 10 días',
            self::ILT_DIAS_SIGUIENTES => 'ILT dias 11 y siguientes',
            self::TRABAJADOR_SINIESTRADO_ART => 'Trabajador siniestrado en nomina de ART',
            self::TRABAJADOR_TEMPORADA => 'Trabajador de temporada Reserva de puesto',
            self::ACTIVO_EXTERIOR => 'Activo - Funciones en el exterior',
            self::LICENCIA_PATERNIDAD => 'Licencia por paternidad',
            self::LICENCIA_FUERZA_MAYOR => 'Licencia por fuerza mayor (art. 221 LCT)',
            self::EMPLEADO_EVENTUAL_MES_COMPLETO => 'Empl. eventual en EU (p/uso ESE) mes completo',
            self::EMPLEADO_EVENTUAL_MES_INCOMPLETO => 'Empl. eventual en EU (p/uso ESE) mes incompleto',
            self::CONSERVACION_EMPLEO_ART_211 => 'Conservación del empleo p/accidente o enf. Inculpable art. 211 LCT',
            self::SUSPENSION_DISCIPLINARIA => 'Suspensiones p/causas disciplinarias',
            self::SUSPENDIDO_RES_397 => 'Suspendido. Res. 397/2020 MTEySS c/Aportes OS',
            self::SUSPENSION_PARCIAL => 'Susp. período parcial L 20744 art.223bis / Res. 397 MTEySS',
            self::DECRETO_792_GRUPO_RIESGO => 'Dto 792/20 - Mayores 60 años, embarazadas, grupo de riesgo',
            self::LICENCIA_LEY_27674 => 'Licencia Ley 27.674 Art. 13 – Régimen De Protección Integral Del Niño, Niña y Adolescente Con Cáncer',
        };
    }

    /**
     * Obtiene el enum a partir de un código numérico.
     */
    public static function fromCodigo(int $codigo): ?self
    {
        return match($codigo) {
            0 => self::BAJA_FALLECIMIENTO,
            1 => self::ACTIVO,
            2 => self::BAJA_OTRAS_CAUSALES,
            3 => self::ACTIVO_DECRETO_796,
            4 => self::BAJA_DECRETO_796,
            5 => self::MATERNIDAD,
            6 => self::SUSPENDIDO,
            7 => self::BAJA_DESPIDO,
            8 => self::BAJA_DESPIDO_DECRETO_796,
            9 => self::SUSPENDIDO_LEY_20744,
            10 => self::LICENCIA_EXCEDENCIA,
            11 => self::LICENCIA_MATERNIDAD_DOWN,
            12 => self::LICENCIA_VACACIONES,
            13 => self::LICENCIA_SIN_GOCE,
            14 => self::RESERVA_PUESTO,
            15 => self::ESE_CESE_TRANSITORIO,
            16 => self::PERSONAL_SINIESTRADO_ART,
            17 => self::REINGRESO_JUDICIAL,
            18 => self::ILT_PRIMEROS_10_DIAS,
            19 => self::ILT_DIAS_SIGUIENTES,
            20 => self::TRABAJADOR_SINIESTRADO_ART,
            21 => self::TRABAJADOR_TEMPORADA,
            31 => self::ACTIVO_EXTERIOR,
            32 => self::LICENCIA_PATERNIDAD,
            33 => self::LICENCIA_FUERZA_MAYOR,
            42 => self::EMPLEADO_EVENTUAL_MES_COMPLETO,
            43 => self::EMPLEADO_EVENTUAL_MES_INCOMPLETO,
            44 => self::CONSERVACION_EMPLEO_ART_211,
            45 => self::SUSPENSION_DISCIPLINARIA,
            48 => self::SUSPENDIDO_RES_397,
            49 => self::SUSPENSION_PARCIAL,
            50 => self::DECRETO_792_GRUPO_RIESGO,
            51 => self::LICENCIA_LEY_27674,
            default => null
        };
    }
}
