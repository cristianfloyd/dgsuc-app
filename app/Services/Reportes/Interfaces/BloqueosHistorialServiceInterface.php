<?php

namespace App\Services\Reportes\Interfaces;

use Illuminate\Support\Collection;
use App\Data\Reportes\TransferResultData;

/**
 * Interface para el servicio de historial de bloqueos
 *
 * Responsabilidad: Definir el contrato para transferir bloqueos procesados al historial
 */
interface BloqueosHistorialServiceInterface
{
    /**
     * Transfiere bloqueos procesados al historial (RepBloqueo)
     *
     * @param Collection $bloqueos Colección de bloqueos a transferir
     * @return TransferResultData Resultado de la transferencia
     */
    public function transferirAlHistorial(Collection $bloqueos): TransferResultData;

    /**
     * Valida que los bloqueos puedan ser transferidos al historial
     *
     * @param Collection $bloqueos Bloqueos a validar
     * @return bool True si pueden ser transferidos
     */
    public function validarTransferencia(Collection $bloqueos): bool;

    /**
     * Obtiene estadísticas del historial para un período fiscal
     *
     * @param string $periodoFiscal Período fiscal en formato YYYYMM
     * @return array Estadísticas del historial
     */
    public function getEstadisticasHistorial(string $periodoFiscal): array;

    /**
     * Obtiene bloqueos ya transferidos al historial por período
     *
     * @param string $periodoFiscal Período fiscal
     * @return Collection Bloqueos en el historial
     */
    public function getBloqueosEnHistorial(string $periodoFiscal): Collection;
}
