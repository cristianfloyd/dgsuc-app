<?php

namespace App\Data\Reportes;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * Data Transfer Object para el resultado de limpieza de la tabla de trabajo.
 */
class CleanupResultData extends Data
{
    public function __construct(
        /** @var bool Indica si la limpieza fue exitosa */
        public readonly bool $success,
        /** @var int Número de registros eliminados */
        public readonly int $eliminados,
        /** @var int Número de registros que no pudieron ser eliminados */
        public readonly int $noEliminados,
        /** @var array Período fiscal procesado ['year' => ..., 'month' => ...] */
        public readonly array $periodoFiscal,
        /** @var Carbon Timestamp del proceso */
        #[WithCast(DateTimeInterfaceCast::class)]
        public readonly Carbon $timestamp,
        /** @var string|null Mensaje descriptivo */
        public readonly ?string $mensaje = null,
        /** @var array Detalles adicionales del proceso */
        public readonly array $detalles = [],
        /** @var array IDs de registros eliminados exitosamente */
        public readonly array $idsEliminados = [],
        /** @var array IDs de registros que no pudieron ser eliminados con razones */
        public readonly array $idsNoEliminados = [],
        /** @var array Estadísticas antes de la limpieza */
        public readonly array $estadisticasAntes = [],
        /** @var array Estadísticas después de la limpieza */
        public readonly array $estadisticasDespues = [],
    ) {
    }

    /**
     * Crea una instancia exitosa.
     */
    public static function success(
        int $eliminados,
        array $periodoFiscal,
        array $idsEliminados = [],
        array $estadisticasAntes = [],
        array $estadisticasDespues = [],
    ): self {
        return new self(
            success: true,
            eliminados: $eliminados,
            noEliminados: 0,
            periodoFiscal: $periodoFiscal,
            timestamp: now(),
            mensaje: "Se eliminaron {$eliminados} registros de la tabla de trabajo exitosamente",
            idsEliminados: $idsEliminados,
            estadisticasAntes: $estadisticasAntes,
            estadisticasDespues: $estadisticasDespues,
        );
    }

    /**
     * Crea una instancia con limpieza parcial.
     */
    public static function partial(
        int $eliminados,
        int $noEliminados,
        array $periodoFiscal,
        array $idsEliminados = [],
        array $idsNoEliminados = [],
    ): self {
        return new self(
            success: $noEliminados === 0,
            eliminados: $eliminados,
            noEliminados: $noEliminados,
            periodoFiscal: $periodoFiscal,
            timestamp: now(),
            mensaje: "Eliminados: {$eliminados}, No eliminados: {$noEliminados}",
            idsEliminados: $idsEliminados,
            idsNoEliminados: $idsNoEliminados,
        );
    }

    /**
     * Crea una instancia de error.
     */
    public static function error(string $mensaje, array $periodoFiscal): self
    {
        return new self(
            success: false,
            eliminados: 0,
            noEliminados: 0,
            periodoFiscal: $periodoFiscal,
            timestamp: now(),
            mensaje: $mensaje,
        );
    }

    /**
     * Crea una instancia cuando no hay registros para limpiar.
     */
    public static function nothingToClean(array $periodoFiscal): self
    {
        $periodoString = $periodoFiscal['year'] . '-' . str_pad((string) $periodoFiscal['month'], 2, '0', \STR_PAD_LEFT);
        return new self(
            success: true,
            eliminados: 0,
            noEliminados: 0,
            periodoFiscal: $periodoFiscal,
            timestamp: now(),
            mensaje: "No hay registros para limpiar en el período {$periodoString}",
        );
    }

    /**
     * Obtiene el total de registros procesados.
     */
    public function getTotalProcesados(): int
    {
        return $this->eliminados + $this->noEliminados;
    }

    /**
     * Obtiene el porcentaje de éxito en la limpieza.
     */
    public function getPorcentajeExito(): float
    {
        $total = $this->getTotalProcesados();
        return $total > 0 ? ($this->eliminados / $total) * 100 : 100;
    }

    /**
     * Verifica si la limpieza fue completa.
     */
    public function esLimpiezaCompleta(): bool
    {
        return $this->success && $this->noEliminados === 0;
    }

    /**
     * Devuelve el período fiscal como string (YYYY-MM).
     */
    public function getPeriodoFiscalString(): string
    {
        return $this->periodoFiscal['year'] . '-' . str_pad((string) $this->periodoFiscal['month'], 2, '0', \STR_PAD_LEFT);
    }
}
