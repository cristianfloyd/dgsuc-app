<?php

namespace App\Contracts;

/**
 * Interface para generadores de archivos SICOSS
 * Define el contrato que deben cumplir las clases generadoras
 */
interface SicossGeneratorInterface
{
    /**
     * Genera archivo SICOSS
     * 
     * @param int|null $numeroLegajo Número de legajo (opcional)
     * @param array $configuracionPersonalizada Configuración adicional
     * @return array Resultado de la operación
     */
    public function generar(?int $numeroLegajo = null, array $configuracionPersonalizada = []): array;
}