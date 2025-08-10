<?php

namespace App\Services\Reportes\Interfaces;

use App\Data\Reportes\TransferResultData;
use Illuminate\Support\Collection;

/**
 * Interface para el servicio de historial de bloqueos.
 *
 * Responsabilidad: Definir el contrato para transferir bloqueos procesados al historial
 */
interface BloqueosHistorialServiceInterface
{
    /**
     * Transfiere bloqueos procesados al historial (RepBloqueo).
     *
     * @param Collection $bloqueos Colección de bloqueos a transferir
     *
     * @return TransferResultData Resultado de la transferencia
     */
    public function transferirAlHistorial(Collection $bloqueos): TransferResultData;

    /**
     * Valida que los bloqueos puedan ser transferidos al historial.
     *
     * @param Collection $bloqueos Bloqueos a validar
     *
     * @return bool True si pueden ser transferidos
     */
    public function validarTransferencia(Collection $bloqueos): bool;

    /**
     * Obtiene estadísticas del historial para un período fiscal.
     *
     * @param array $periodoFiscal Período fiscal ['year' => ..., 'month' => ...]
     *
     * @return array Estadísticas del historial
     */
    public function getEstadisticasHistorial(array $periodoFiscal): array;

    /**
     * Obtiene bloqueos ya transferidos al historial por período.
     *
     * @param array $periodoFiscal Período fiscal ['year' => ..., 'month' => ...]
     *
     * @return Collection Bloqueos en el historial
     */
    public function getBloqueosEnHistorial(array $periodoFiscal): Collection;
}
