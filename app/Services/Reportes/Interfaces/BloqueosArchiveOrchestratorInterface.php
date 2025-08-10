<?php

namespace App\Services\Reportes\Interfaces;

use App\Data\Reportes\ArchiveProcessData;

/**
 * Interface para el servicio orquestador de archivado de bloqueos.
 *
 * Responsabilidad: Definir el contrato para coordinar el proceso completo de archivado
 */
interface BloqueosArchiveOrchestratorInterface
{
    /**
     * Archiva un período fiscal completo (transferencia + limpieza).
     *
     * @param array $periodoFiscal Período fiscal en formato ['year' => 'YYYY', 'month' => 'MM']
     *
     * @return ArchiveProcessData Resultado del proceso completo
     */
    public function archivarPeriodoCompleto(array $periodoFiscal): ArchiveProcessData;

    /**
     * Valida que se pueda realizar el archivado completo.
     *
     * @param array $periodoFiscal Período fiscal en formato ['year' => 'YYYY', 'month' => 'MM']
     *
     * @return bool True si se puede archivar
     */
    public function validarArchivado(array $periodoFiscal): bool;

    /**
     * Obtiene un resumen del estado actual para archivado.
     *
     * @param array $periodoFiscal Período fiscal en formato ['year' => 'YYYY', 'month' => 'MM']
     *
     * @return array Resumen del estado
     */
    public function getResumenEstadoArchivado(array $periodoFiscal): array;

    /**
     * Verifica si un período ya fue archivado.
     *
     * @param array $periodoFiscal Período fiscal en formato ['year' => 'YYYY', 'month' => 'MM']
     *
     * @return bool True si ya fue archivado
     */
    public function periodoYaArchivado(array $periodoFiscal): bool;
}
