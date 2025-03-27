<?php

namespace App\Contracts;

use App\ValueObjects\NroLiqui;

/**
 * Define una interfaz para un servicio que ejecuta una operación de simplificación de la liquidación Mapuche para un número de liquidación y período fiscal específicos.
 * @method bool execute(int $nroLiqui, int $periodoFiscal): bool
 * @method bool isNotEmpty() Verdadero si la instancia actual no está vacía, falso en caso contrario.
 *
 * @author Equipo de desarrollo de Mapuche
 * @version 1.0
 */
interface MapucheMiSimplificacionServiceInterface
{
    /**
     * Ejecuta una operación de simplificación de la liquidación Mapuche para un número de liquidación y período fiscal específicos.
     *
     * @param NroLiqui $nroLiqui Número de liquidación a simplificar.
     * @param int $periodoFiscal Período fiscal de la liquidación a simplificar.
     * @return bool Verdadero si la operación de simplificación se ejecutó correctamente, falso en caso contrario.
     */
    public function execute(NroLiqui $nroLiqui,int $periodoFiscal): bool;

    /**
     * Determina si la instancia actual no está vacía.
     *
     * @return bool Verdadero si la instancia no está vacía, falso en caso contrario.
     */
    public function isNotEmpty(): bool;
}
