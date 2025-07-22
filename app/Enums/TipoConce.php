<?php

namespace App\Enums;

enum TipoConce: string
{
    /**
     * Remuneración con aporte
     * Ejemplo: Sueldo Básico.
     */
    case REMUNERACION_CON_APORTE = 'C';

    /**
     * Remuneración sin Aporte
     * Ejemplo: Ajuste de Haberes.
     */
    case REMUNERACION_SIN_APORTE = 'S';

    /**
     * Salario Familiar
     * Ejemplo: Asignación por Hijo.
     */
    case SALARIO_FAMILIAR = 'F';

    /**
     * Descuento
     * Ejemplo: Obra Social.
     */
    case DESCUENTO = 'D';

    /**
     * Aporte Patronal
     * Ejemplo: Obra Social Patronal.
     */
    case APORTE_PATRONAL = 'A';

    /**
     * Otro No remunerativo
     * Ejemplo: Becas.
     */
    case OTRO_NO_REMUNERATIVO = 'O';

    /**
     * Obtiene la descripción del tipo de concepto.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::REMUNERACION_CON_APORTE => 'Remuneración con aporte',
            self::REMUNERACION_SIN_APORTE => 'Remuneración sin aporte',
            self::SALARIO_FAMILIAR => 'Salario Familiar',
            self::DESCUENTO => 'Descuento',
            self::APORTE_PATRONAL => 'Aporte Patronal',
            self::OTRO_NO_REMUNERATIVO => 'Otro No remunerativo',
        };
    }

    /**
     * Obtiene el color de badge para el tipo de concepto.
     */
    public function getBadgeColor(): string
    {
        return match ($this) {
            self::REMUNERACION_CON_APORTE => 'success',
            self::REMUNERACION_SIN_APORTE => 'warning',
            self::SALARIO_FAMILIAR => 'info',
            self::DESCUENTO => 'danger',
            self::APORTE_PATRONAL => 'primary',
            self::OTRO_NO_REMUNERATIVO => 'gray',
        };
    }

    /**
     * Determina si el concepto es remunerativo.
     */
    public function isRemunerativo(): bool
    {
        return \in_array($this, [
            self::REMUNERACION_CON_APORTE,
            self::REMUNERACION_SIN_APORTE,
        ]);
    }

    /**
     * Determina si el concepto lleva aportes.
     */
    public function tieneAportes(): bool
    {
        return $this === self::REMUNERACION_CON_APORTE;
    }

    /**
     * Determina si el concepto es un descuento o aporte patronal.
     */
    public function esDescuentoOAporte(): bool
    {
        return \in_array($this, [
            self::DESCUENTO,
            self::APORTE_PATRONAL,
        ]);
    }

    /**
     * Obtiene un ejemplo del tipo de concepto.
     */
    public function getExample(): string
    {
        return match ($this) {
            self::REMUNERACION_CON_APORTE => 'Sueldo Básico',
            self::REMUNERACION_SIN_APORTE => 'Ajuste de Haberes',
            self::SALARIO_FAMILIAR => 'Asignación por Hijo',
            self::DESCUENTO => 'Obra Social',
            self::APORTE_PATRONAL => 'Obra Social Patronal',
            self::OTRO_NO_REMUNERATIVO => 'Becas',
        };
    }
}
