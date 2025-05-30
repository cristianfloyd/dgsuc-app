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
     * @param array $periodoFiscal Período fiscal a limpiar ['year' => ..., 'month' => ...]
     * @return CleanupResultData Resultado de la limpieza
     */
    public function limpiarTablaWork(array $periodoFiscal): CleanupResultData;

    /**
     * Valida que se pueda realizar la limpieza de forma segura
     *
     * @param array $periodoFiscal Período fiscal a validar
     * @return bool True si se puede limpiar de forma segura
     */
    public function validarLimpieza(array $periodoFiscal): bool;

    /**
     * Obtiene registros pendientes que no pueden ser eliminados
     *
     * @param array|null $periodoFiscal Período fiscal opcional
     * @return Collection Registros pendientes
     */
    public function getRegistrosPendientes(?array $periodoFiscal = null): Collection;

    /**
     * Obtiene registros listos para ser eliminados
     *
     * @param array $periodoFiscal Período fiscal
     * @return Collection Registros que pueden ser eliminados
     */
    public function getRegistrosListosParaEliminar(array $periodoFiscal): Collection;

    /**
     * Cuenta registros por estado para un período
     *
     * @param array $periodoFiscal Período fiscal
     * @return array Conteo por estado
     */
    public function contarRegistrosPorEstado(array $periodoFiscal): array;
}
