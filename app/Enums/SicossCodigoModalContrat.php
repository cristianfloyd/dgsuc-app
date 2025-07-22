<?php

namespace App\Enums;

enum SicossCodigoModalContrat: int
{
    case CONTRATO_MODALIDAD_PROMOVIDA = 0;

    case TIEMPO_PARCIAL_INDETERMINADO = 1;

    case BECARIOS_RESIDENCIAS = 2;

    case APRENDIZAJE = 3;

    case ESPECIAL_FOMENTO_EMPLEO = 4;

    case FOMENTO_EMPLEO = 5;

    case LANZAMIENTO_NUEVA_ACTIVIDAD = 6;

    case PERIODO_PRUEBA = 7;

    case TIEMPO_COMPLETO_INDETERMINADO = 8;

    case PRACTICA_LABORAL_JOVENES = 9;

    case PASANTIAS = 10;

    case TRABAJO_TEMPORADA = 11;

    case TRABAJO_EVENTUAL = 12;

    case TRABAJO_FORMACION = 13;

    case NUEVO_PERIODO_PRUEBA = 14;

    case PUESTO_NUEVO_25_44 = 15;

    case PERIODO_PRUEBA_DISCAPACITADO = 16;

    case PUESTO_NUEVO_ESPECIAL = 17;

    case TRABAJADOR_DISCAPACITADO = 18;

    case PUESTO_NUEVO_25_44_DISCAPACITADO = 19;

    case PUESTO_NUEVO_ESPECIAL_DISCAPACITADO = 20;

    case TIEMPO_PARCIAL_DETERMINADO = 21;

    case TIEMPO_COMPLETO_DETERMINADO = 22;

    case PERSONAL_NO_PERMANENTE = 23;

    case PERSONAL_CONSTRUCCION = 24;

    case EMPLEO_PUBLICO_PROVINCIAL = 25;

    public function descripcion(): string
    {
        return match($this) {
            self::CONTRATO_MODALIDAD_PROMOVIDA => 'Contrato Modalidad Promovida. Reducción 0%',
            self::TIEMPO_PARCIAL_INDETERMINADO => 'A tiempo parcial: Indeterminado /permanente',
            self::BECARIOS_RESIDENCIAS => 'Becarios- Residencias médicas Ley 22127',
            self::APRENDIZAJE => 'De aprendizaje l.25013',
            self::ESPECIAL_FOMENTO_EMPLEO => 'Especial de Fomento del Empleo: L.24465',
            self::FOMENTO_EMPLEO => 'Fomento del empleo. L.24013 y 24465',
            self::LANZAMIENTO_NUEVA_ACTIVIDAD => 'Lanzamiento nueva actividad. Idem 005',
            self::PERIODO_PRUEBA => 'Período de prueba. Leyes 24465 y 25013',
            self::TIEMPO_COMPLETO_INDETERMINADO => 'A Tiempo completo indeterminado /Trabajo permanente',
            self::PRACTICA_LABORAL_JOVENES => 'Práctica laboral para jovenes',
            self::PASANTIAS => 'Pasantías. Ley N° 25165 . Dec 340/92',
            self::TRABAJO_TEMPORADA => 'Trabajo de temporada',
            self::TRABAJO_EVENTUAL => 'Trabajo eventual',
            self::TRABAJO_FORMACION => 'Trabajo formación',
            self::NUEVO_PERIODO_PRUEBA => 'Nuevo Período de Prueba',
            self::PUESTO_NUEVO_25_44 => 'Puesto Nuevo Varones y Mujeres de 25 a 44 años Ley 25250',
            self::PERIODO_PRUEBA_DISCAPACITADO => 'Nuevo Periodo de Prueba Trabajador Discapacitado Art.34 Ley 24147',
            self::PUESTO_NUEVO_ESPECIAL => 'Puesto Nuevo menor de 25 años, Varones y Mujeres de 45 o más años y Mujer Jefe de flia. S/límite/edad Ley 25250',
            self::TRABAJADOR_DISCAPACITADO => 'Trabajador Discapacitado Art. 34. Ley 24147',
            self::PUESTO_NUEVO_25_44_DISCAPACITADO => 'Puesto Nuevo. Varones y Mujeres 25 a 44 años Art. 34. Ley 24147 Ley 25250',
            self::PUESTO_NUEVO_ESPECIAL_DISCAPACITADO => 'Pto. Nuevo Menor 25 años, Varones y Mujeres 45 ó más y Mujer Jefe de flia. S/límite de edad. Art 34 L. 24147Ley 25250',
            self::TIEMPO_PARCIAL_DETERMINADO => 'A tiempo parcial determinado (contrato a plazo fijo)',
            self::TIEMPO_COMPLETO_DETERMINADO => 'A Tiempo completo determinado (contrato a plazo fijo)',
            self::PERSONAL_NO_PERMANENTE => 'Personal no permanente L 22248',
            self::PERSONAL_CONSTRUCCION => 'Personal de la Construcción L 22250',
            self::EMPLEO_PUBLICO_PROVINCIAL => 'Empleo público provincial',
        };
    }

    /**
     * Obtiene el enum a partir de un código numérico.
     */
    public static function fromCodigo(int $codigo): ?self
    {
        return match($codigo) {
            0 => self::CONTRATO_MODALIDAD_PROMOVIDA,
            1 => self::TIEMPO_PARCIAL_INDETERMINADO,
            2 => self::BECARIOS_RESIDENCIAS,
            3 => self::APRENDIZAJE,
            4 => self::ESPECIAL_FOMENTO_EMPLEO,
            5 => self::FOMENTO_EMPLEO,
            6 => self::LANZAMIENTO_NUEVA_ACTIVIDAD,
            7 => self::PERIODO_PRUEBA,
            8 => self::TIEMPO_COMPLETO_INDETERMINADO,
            9 => self::PRACTICA_LABORAL_JOVENES,
            10 => self::PASANTIAS,
            11 => self::TRABAJO_TEMPORADA,
            12 => self::TRABAJO_EVENTUAL,
            13 => self::TRABAJO_FORMACION,
            14 => self::NUEVO_PERIODO_PRUEBA,
            15 => self::PUESTO_NUEVO_25_44,
            16 => self::PERIODO_PRUEBA_DISCAPACITADO,
            17 => self::PUESTO_NUEVO_ESPECIAL,
            18 => self::TRABAJADOR_DISCAPACITADO,
            19 => self::PUESTO_NUEVO_25_44_DISCAPACITADO,
            20 => self::PUESTO_NUEVO_ESPECIAL_DISCAPACITADO,
            21 => self::TIEMPO_PARCIAL_DETERMINADO,
            22 => self::TIEMPO_COMPLETO_DETERMINADO,
            23 => self::PERSONAL_NO_PERMANENTE,
            24 => self::PERSONAL_CONSTRUCCION,
            25 => self::EMPLEO_PUBLICO_PROVINCIAL,
            default => null
        };
    }
}
