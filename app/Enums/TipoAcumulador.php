<?php

namespace App\Enums;

enum TipoAcumulador: string
{
    /**
     * Acumuladores de cargos
     * Solo pueden utilizarse en conceptos de cargo.
     */
    case CARGO = 'C';

    /**
     * Acumuladores de mes
     * Suman valores de todas las liquidaciones del mes (menos la corriente).
     */
    case MES = 'M';

    /**
     * Acumuladores de legajo
     * Pueden usarse tanto en conceptos de Legajo como de Cargo.
     */
    case LEGAJO = 'L';

    /**
     * Acumuladores retroactivos
     * Solo acumulan resultados de conceptos con fechas de reajuste.
     */
    case RETROACTIVO = 'R';

    public function getDescription(): string
    {
        return match($this) {
            self::CARGO => 'Solo pueden utilizarse en conceptos de cargo',
            self::MES => 'Suman valores de todas las liquidaciones del mes (menos la corriente)',
            self::LEGAJO => 'Pueden usarse tanto en conceptos de Legajo como de Cargo',
            self::RETROACTIVO => 'Solo acumulan resultados de conceptos con fechas de reajuste',
        };
    }

    public function getPrefix(): string
    {
        return match($this) {
            self::CARGO => 'Acum',
            self::MES => '+Acum',
            self::LEGAJO => 'L:Acum',
            self::RETROACTIVO => 'R:Acum',
        };
    }
}
