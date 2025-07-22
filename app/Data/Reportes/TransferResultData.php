<?php

namespace App\Data\Reportes;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * Data Transfer Object para el resultado de transferencia al historial.
 */
class TransferResultData extends Data
{
    public function __construct(
        /** @var bool Indica si la transferencia fue exitosa */
        public readonly bool $success,

        /** @var int Número de registros transferidos */
        public readonly int $transferidos,

        /** @var int Número de registros que fallaron */
        public readonly int $fallidos,

        /** @var array Período fiscal procesado ['year' => ..., 'month' => ...] */
        public readonly array $periodoFiscal,

        /** @var Carbon Timestamp del proceso */
        #[WithCast(DateTimeInterfaceCast::class)]
        public readonly Carbon $timestamp,

        /** @var string|null Mensaje descriptivo */
        public readonly ?string $mensaje = null,

        /** @var array Detalles adicionales del proceso */
        public readonly array $detalles = [],

        /** @var array IDs de registros transferidos exitosamente */
        public readonly array $idsTransferidos = [],

        /** @var array IDs de registros que fallaron con sus errores */
        public readonly array $idsFallidos = [],
    ) {
    }

    /**
     * Crea una instancia exitosa.
     */
    public static function success(
        int $transferidos,
        array $periodoFiscal,
        array $idsTransferidos = [],
        array $detalles = [],
    ): self {
        return new self(
            success: true,
            transferidos: $transferidos,
            fallidos: 0,
            periodoFiscal: $periodoFiscal,
            timestamp: now(),
            mensaje: "Se transfirieron {$transferidos} registros al historial exitosamente",
            detalles: $detalles,
            idsTransferidos: $idsTransferidos,
        );
    }

    /**
     * Crea una instancia con errores parciales.
     */
    public static function partial(
        int $transferidos,
        int $fallidos,
        array $periodoFiscal,
        array $idsTransferidos = [],
        array $idsFallidos = [],
    ): self {
        return new self(
            success: $fallidos === 0,
            transferidos: $transferidos,
            fallidos: $fallidos,
            periodoFiscal: $periodoFiscal,
            timestamp: now(),
            mensaje: "Transferidos: {$transferidos}, Fallidos: {$fallidos}",
            idsTransferidos: $idsTransferidos,
            idsFallidos: $idsFallidos,
        );
    }

    /**
     * Crea una instancia de error completo.
     */
    public static function error(string $mensaje, array $periodoFiscal): self
    {
        return new self(
            success: false,
            transferidos: 0,
            fallidos: 0,
            periodoFiscal: $periodoFiscal,
            timestamp: now(),
            mensaje: $mensaje,
        );
    }

    /**
     * Obtiene el total de registros procesados.
     */
    public function getTotalProcesados(): int
    {
        return $this->transferidos + $this->fallidos;
    }

    /**
     * Obtiene el porcentaje de éxito.
     */
    public function getPorcentajeExito(): float
    {
        $total = $this->getTotalProcesados();
        return $total > 0 ? ($this->transferidos / $total) * 100 : 0;
    }

    /**
     * Devuelve el período fiscal como string (YYYY-MM).
     */
    public function getPeriodoFiscalString(): string
    {
        return $this->periodoFiscal['year'] . '-' . str_pad($this->periodoFiscal['month'], 2, '0', \STR_PAD_LEFT);
    }
}
