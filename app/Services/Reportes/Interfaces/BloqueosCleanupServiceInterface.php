<?php

namespace App\Services\Reportes\Interfaces;

use Illuminate\Support\Collection;
use App\Data\Reportes\CleanupResultData;

/**
 * Interface para el servicio de limpieza de bloqueos
 *
 * Responsabilidad: Definir el contrato para limpiar la tabla de trabajo de bloqueos
 */
interface BloqueosCleanupServiceInterface
{
    /**
     * Limpia la tabla de trabajo eliminando registros ya transferidos al historial
     *
     * @param string $periodoFiscal Período fiscal a limpiar
     * @return CleanupResultData Resultado de la limpieza
     */
    public function limpiarTablaWork(string $periodoFiscal): CleanupResultData;

    /**
     * Valida que se pueda realizar la limpieza de forma segura
     *
     * @param string $periodoFiscal Período fiscal a validar
     * @return bool True si se puede limpiar de forma segura
     */
    public function validarLimpieza(string $periodoFiscal): bool;

    /**
     * Obtiene registros pendientes que no pueden ser eliminados
     *
     * @param string|null $periodoFiscal Período fiscal opcional
     * @return Collection Registros pendientes
     */
    public function getRegistrosPendientes(?string $periodoFiscal = null): Collection;

    /**
     * Obtiene registros listos para ser eliminados
     *
     * @param string $periodoFiscal Período fiscal
     * @return Collection Registros que pueden ser eliminados
     */
    public function getRegistrosListosParaEliminar(string $periodoFiscal): Collection;

    /**
     * Cuenta registros por estado para un período
     *
     * @param string $periodoFiscal Período fiscal
     * @return array Conteo por estado
     */
    public function contarRegistrosPorEstado(string $periodoFiscal): array;
}
