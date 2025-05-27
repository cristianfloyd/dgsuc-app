<?php

namespace App\Data\Reportes;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

/**
 * Data Transfer Object para el resultado del proceso completo de archivado
 */
class ArchiveProcessData extends Data
{
    public function __construct(
        /** @var bool Indica si el proceso completo fue exitoso */
        public readonly bool $success,

        /** @var string Período fiscal procesado */
        public readonly string $periodoFiscal,

        /** @var Carbon Timestamp del proceso */
        #[WithCast(DateTimeInterfaceCast::class)]
        public readonly Carbon $timestamp,

        /** @var TransferResultData Resultado de la transferencia al historial */
        public readonly TransferResultData $transferResult,

        /** @var CleanupResultData Resultado de la limpieza */
        public readonly CleanupResultData $cleanupResult,

        /** @var string|null Mensaje general del proceso */
        public readonly ?string $mensaje = null,

        /** @var array Resumen estadístico del proceso */
        public readonly array $resumen = [],

        /** @var array Detalles adicionales del proceso completo */
        public readonly array $detalles = [],

        /** @var float Duración del proceso en segundos */
        public readonly float $duracionSegundos = 0.0
    ) {}

    /**
     * Crea una instancia exitosa
     */
    public static function success(
        string $periodoFiscal,
        TransferResultData $transferResult,
        CleanupResultData $cleanupResult,
        float $duracionSegundos = 0.0
    ): self {
        $totalTransferidos = $transferResult->transferidos;
        $totalEliminados = $cleanupResult->eliminados;

        return new self(
            success: true,
            periodoFiscal: $periodoFiscal,
            timestamp: now(),
            transferResult: $transferResult,
            cleanupResult: $cleanupResult,
            mensaje: "Período {$periodoFiscal} archivado exitosamente. Transferidos: {$totalTransferidos}, Eliminados: {$totalEliminados}",
            resumen: [
                'periodo_fiscal' => $periodoFiscal,
                'registros_transferidos' => $totalTransferidos,
                'registros_eliminados' => $totalEliminados,
                'proceso_completo' => true,
                'duracion_segundos' => $duracionSegundos
            ],
            duracionSegundos: $duracionSegundos
        );
    }

    /**
     * Crea una instancia con errores parciales
     */
    public static function partial(
        string $periodoFiscal,
        TransferResultData $transferResult,
        CleanupResultData $cleanupResult,
        ?string $mensaje = null
    ): self {
        return new self(
            success: false,
            periodoFiscal: $periodoFiscal,
            timestamp: now(),
            transferResult: $transferResult,
            cleanupResult: $cleanupResult,
            mensaje: $mensaje ?? 'El proceso de archivado se completó con errores parciales',
            resumen: [
                'periodo_fiscal' => $periodoFiscal,
                'registros_transferidos' => $transferResult->transferidos,
                'registros_fallidos_transferencia' => $transferResult->fallidos,
                'registros_eliminados' => $cleanupResult->eliminados,
                'registros_no_eliminados' => $cleanupResult->noEliminados,
                'proceso_completo' => false
            ]
        );
    }

    /**
     * Crea una instancia de error completo
     */
    public static function error(
        string $periodoFiscal,
        string $mensaje,
        ?TransferResultData $transferResult = null,
        ?CleanupResultData $cleanupResult = null
    ): self {
        return new self(
            success: false,
            periodoFiscal: $periodoFiscal,
            timestamp: now(),
            transferResult: $transferResult ?? TransferResultData::error($mensaje, $periodoFiscal),
            cleanupResult: $cleanupResult ?? CleanupResultData::error($mensaje, $periodoFiscal),
            mensaje: $mensaje,
            resumen: [
                'periodo_fiscal' => $periodoFiscal,
                'error' => true,
                'mensaje_error' => $mensaje
            ]
        );
    }

    /**
     * Obtiene el total de registros procesados en ambas fases
     */
    public function getTotalRegistrosProcesados(): int
    {
        return $this->transferResult->getTotalProcesados() + $this->cleanupResult->getTotalProcesados();
    }

    /**
     * Verifica si el proceso fue completamente exitoso
     */
    public function esProcesoCompleto(): bool
    {
        return $this->success &&
               $this->transferResult->success &&
               $this->cleanupResult->success;
    }

    /**
     * Obtiene un resumen textual del proceso
     */
    public function getResumenTextual(): string
    {
        if (!$this->success) {
            return "Error en el archivado del período {$this->periodoFiscal}: {$this->mensaje}";
        }

        $transferidos = $this->transferResult->transferidos;
        $eliminados = $this->cleanupResult->eliminados;
        $duracion = number_format($this->duracionSegundos, 2);

        return "Período {$this->periodoFiscal} archivado exitosamente en {$duracion}s. " .
               "Transferidos al historial: {$transferidos}, Eliminados de trabajo: {$eliminados}";
    }

    /**
     * Obtiene estadísticas detalladas
     */
    public function getEstadisticasDetalladas(): array
    {
        return [
            'proceso' => [
                'exitoso' => $this->success,
                'periodo_fiscal' => $this->periodoFiscal,
                'timestamp' => $this->timestamp->toISOString(),
                'duracion_segundos' => $this->duracionSegundos
            ],
            'transferencia' => [
                'exitosa' => $this->transferResult->success,
                'transferidos' => $this->transferResult->transferidos,
                'fallidos' => $this->transferResult->fallidos,
                'porcentaje_exito' => $this->transferResult->getPorcentajeExito()
            ],
            'limpieza' => [
                'exitosa' => $this->cleanupResult->success,
                'eliminados' => $this->cleanupResult->eliminados,
                'no_eliminados' => $this->cleanupResult->noEliminados,
                'porcentaje_exito' => $this->cleanupResult->getPorcentajeExito()
            ]
        ];
    }
}
