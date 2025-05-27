<?php

namespace App\Services\Reportes\Interfaces;

use App\Data\Reportes\ArchiveProcessData;

/**
 * Interface para el servicio orquestador de archivado de bloqueos
 *
 * Responsabilidad: Definir el contrato para coordinar el proceso completo de archivado
 */
interface BloqueosArchiveOrchestratorInterface
{
    /**
     * Archiva un período fiscal completo (transferencia + limpieza)
     *
     * @param string $periodoFiscal Período fiscal en formato YYYYMM
     * @return ArchiveProcessData Resultado del proceso completo
     */
    public function archivarPeriodoCompleto(string $periodoFiscal): ArchiveProcessData;

    /**
     * Valida que se pueda realizar el archivado completo
     *
     * @param string $periodoFiscal Período fiscal a validar
     * @return bool True si se puede archivar
     */
    public function validarArchivado(string $periodoFiscal): bool;

    /**
     * Obtiene un resumen del estado actual para archivado
     *
     * @param string $periodoFiscal Período fiscal
     * @return array Resumen del estado
     */
    public function getResumenEstadoArchivado(string $periodoFiscal): array;

    /**
     * Verifica si un período ya fue archivado
     *
     * @param string $periodoFiscal Período fiscal
     * @return bool True si ya fue archivado
     */
    public function periodoYaArchivado(string $periodoFiscal): bool;
}
