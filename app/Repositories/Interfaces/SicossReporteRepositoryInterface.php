<?php

namespace App\Repositories\Interfaces;

use Illuminate\Support\Collection;

/**
 * Interfaz SicossReporteRepositoryInterface.
 *
 * Provee la especificación de los métodos que debe implementar
 * un repositorio encargado de generar y consultar el reporte SICOSS.
 *
 * Define operaciones para:
 * - Obtener los datos del reporte para un período fiscal determinado.
 * - Obtener los totales del reporte para un período fiscal.
 * - Verificar la existencia de datos para un período fiscal.
 * - Listar los períodos fiscales disponibles.
 *
 * @package App\Repositories\Interfaces
 */
interface SicossReporteRepositoryInterface
{
    /**
     * Obtiene los datos del reporte SICOSS para el período especificado.
     *
     * @param string $anio Año del período fiscal
     * @param string $mes Mes del período fiscal
     *
     * @return Collection Colección de registros del reporte
     */
    public function getReporte(string $anio, string $mes): Collection;

    /**
     * Obtiene los totales del reporte SICOSS para el período especificado.
     *
     * @param string $anio Año del período fiscal
     * @param string $mes Mes del período fiscal
     *
     * @return array Totales del reporte
     */
    public function getTotales(string $anio, string $mes): array;

    /**
     * Verifica si existen datos para un período fiscal específico.
     *
     * @param string $anio Año del período fiscal
     * @param string $mes Mes del período fiscal
     *
     * @return bool True si existen datos para el período, false en caso contrario
     */
    public function existenDatosParaPeriodo(string $anio, string $mes): bool;

    /**
     * Obtiene los períodos fiscales disponibles en el sistema.
     *
     * @return Collection Períodos fiscales disponibles
     */
    public function getPeriodosFiscalesDisponibles(): Collection;
}
