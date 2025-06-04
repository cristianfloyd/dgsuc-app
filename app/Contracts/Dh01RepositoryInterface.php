<?php

namespace App\Contracts;

interface Dh01RepositoryInterface
{
    /**
     * Obtiene SQL parametrizado para consultar legajos con sus datos relacionados
     *
     * @param string $tabla Tabla base para la consulta (conceptos_liquidados o mapuche.dh01)
     * @param int $valor Valor para el campo licencia
     * @param string $where Condición WHERE adicional
     * @param string $codc_reparto Código de reparto para la condición de régimen
     * @return string SQL query string
     */
    public function getSqlLegajos(string $tabla, int $valor, string $where = 'true', string $codc_reparto = ''): string;
}
