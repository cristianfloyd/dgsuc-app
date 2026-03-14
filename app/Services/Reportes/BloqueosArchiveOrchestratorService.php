<?php

namespace App\Services\Reportes;

use App\Data\Reportes\ArchiveProcessData;
use App\Data\Reportes\CleanupResultData;
use App\Data\Reportes\TransferResultData;
use App\Enums\BloqueosEstadoEnum;
use App\Models\Mapuche\Bloqueos\RepBloqueo;
use App\Models\Reportes\BloqueosDataModel;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Services\Reportes\Interfaces\BloqueosArchiveOrchestratorInterface;
use App\Services\Reportes\Interfaces\BloqueosCleanupServiceInterface;
use App\Services\Reportes\Interfaces\BloqueosHistorialServiceInterface;
use App\Traits\MapucheConnectionTrait;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio orquestador para el proceso completo de archivado de bloqueos.
 *
 * Responsabilidad: Coordinar la transferencia al historial y limpieza de la tabla de trabajo
 */
class BloqueosArchiveOrchestratorService implements BloqueosArchiveOrchestratorInterface
{
    use MapucheConnectionTrait;

    public function __construct(
        private readonly BloqueosHistorialServiceInterface $historialService,
        private readonly BloqueosCleanupServiceInterface $cleanupService,
    ) {}

    /**
     * Archiva un período fiscal completo (transferencia + limpieza).
     */
    public function archivarPeriodoCompleto(array $periodoFiscal): ArchiveProcessData
    {
        $startTime = microtime(true);

        Log::info('Iniciando proceso completo de archivado', [
            'periodo_fiscal' => $periodoFiscal,
        ]);

        try {
            // Validar que se puede realizar el archivado
            if (!$this->validarArchivado($periodoFiscal)) {
                return ArchiveProcessData::error(
                    $periodoFiscal,
                    'No se puede realizar el archivado del período fiscal',
                );
            }

            return DB::connection($this->getConnectionName())->transaction(function () use ($periodoFiscal, $startTime): \App\Data\Reportes\ArchiveProcessData {

                // Paso 1: Obtener registros a procesar
                $registrosParaProcesar = $this->obtenerRegistrosParaProcesar($periodoFiscal);

                if ($registrosParaProcesar->isEmpty()) {
                    Log::info('No hay registros para archivar', [
                        'periodo_fiscal' => $periodoFiscal,
                    ]);

                    return ArchiveProcessData::success(
                        $periodoFiscal,
                        TransferResultData::success(0, $periodoFiscal),
                        CleanupResultData::nothingToClean($periodoFiscal),
                        microtime(true) - $startTime,
                    );
                }

                Log::info('Registros encontrados para archivar', [
                    'periodo_fiscal' => $periodoFiscal,
                    'cantidad' => $registrosParaProcesar->count(),
                ]);

                // Paso 2: Transferir al historial
                Log::info('Iniciando transferencia al historial');
                $transferResultData = $this->historialService->transferirAlHistorial($registrosParaProcesar);

                if (!$transferResultData->success) {
                    Log::error('Error en la transferencia al historial', [
                        'periodo_fiscal' => $periodoFiscal,
                        'mensaje' => $transferResultData->mensaje,
                    ]);

                    return ArchiveProcessData::error(
                        $periodoFiscal,
                        "Error en transferencia: {$transferResultData->mensaje}",
                        $transferResultData,
                    );
                }

                Log::info('Transferencia al historial completada', [
                    'transferidos' => $transferResultData->transferidos,
                    'fallidos' => $transferResultData->fallidos,
                ]);

                // Paso 3: Limpiar tabla de trabajo
                Log::info('Iniciando limpieza de tabla de trabajo');
                $cleanupResultData = $this->cleanupService->limpiarTablaWork($periodoFiscal);

                if (!$cleanupResultData->success) {
                    Log::warning('Error en la limpieza, pero transferencia fue exitosa', [
                        'periodo_fiscal' => $periodoFiscal,
                        'mensaje_limpieza' => $cleanupResultData->mensaje,
                    ]);

                    return ArchiveProcessData::partial(
                        $periodoFiscal,
                        $transferResultData,
                        $cleanupResultData,
                        'Transferencia exitosa pero limpieza falló parcialmente',
                    );
                }

                Log::info('Limpieza de tabla de trabajo completada', [
                    'eliminados' => $cleanupResultData->eliminados,
                    'no_eliminados' => $cleanupResultData->noEliminados,
                ]);

                $endTime = microtime(true);
                $duration = $endTime - $startTime;

                Log::info('Proceso completo de archivado finalizado exitosamente', [
                    'periodo_fiscal' => $periodoFiscal,
                    'transferidos' => $transferResultData->transferidos,
                    'eliminados' => $cleanupResultData->eliminados,
                    'duracion_segundos' => $duration,
                ]);

                return ArchiveProcessData::success(
                    $periodoFiscal,
                    $transferResultData,
                    $cleanupResultData,
                    $duration,
                );
            });
        } catch (Exception $e) {
            Log::error('Error general en proceso de archivado', [
                'periodo_fiscal' => $periodoFiscal,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ArchiveProcessData::error(
                $periodoFiscal,
                "Error en el proceso de archivado: {$e->getMessage()}",
            );
        }
    }

    /**
     * Valida que se pueda realizar el archivado completo.
     */
    public function validarArchivado(array $periodoFiscal): bool
    {
        try {
            Log::info('Validando archivado para período fiscal', [
                'periodo_fiscal' => $periodoFiscal,
            ]);

            // Verificar que existan registros para el período
            $totalRegistros = BloqueosDataModel::query()->where('nro_liqui', $periodoFiscal)->count();
            if ($totalRegistros === 0) {
                Log::info('No existen registros para archivar', [
                    'periodo_fiscal' => $periodoFiscal,
                ]);

                return true; // No hay nada que archivar, es válido
            }

            // Verificar que no esté ya archivado
            if ($this->periodoYaArchivado($periodoFiscal)) {
                Log::warning('El período ya fue archivado previamente', [
                    'periodo_fiscal' => $periodoFiscal,
                ]);

                return false;
            }

            // Verificar que haya registros procesados
            $registrosProcesados = BloqueosDataModel::query()->where('nro_liqui', $periodoFiscal)
                ->where('esta_procesado', true)
                ->count();

            if ($registrosProcesados === 0) {
                Log::error('No hay registros procesados para archivar', [
                    'periodo_fiscal' => $periodoFiscal,
                    'total_registros' => $totalRegistros,
                ]);

                return false;
            }

            // Validar usando los servicios individuales
            $registrosParaProcesar = $this->obtenerRegistrosParaProcesar($periodoFiscal);

            if (!$this->historialService->validarTransferencia($registrosParaProcesar)) {
                Log::error('Validación de transferencia falló', [
                    'periodo_fiscal' => $periodoFiscal,
                ]);

                return false;
            }

            if (!$this->cleanupService->validarLimpieza($periodoFiscal)) {
                Log::error('Validación de limpieza falló', [
                    'periodo_fiscal' => $periodoFiscal,
                ]);

                return false;
            }

            Log::info('Validación de archivado exitosa', [
                'periodo_fiscal' => $periodoFiscal,
                'registros_a_procesar' => $registrosParaProcesar->count(),
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Error en validación de archivado', [
                'periodo_fiscal' => $periodoFiscal,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Obtiene un resumen del estado actual para archivado.
     */
    public function getResumenEstadoArchivado(array $periodoFiscal): array
    {
        try {
            $totalRegistros = BloqueosDataModel::query()->where('nro_liqui', $periodoFiscal)->count();
            $registrosProcesados = BloqueosDataModel::query()->where('nro_liqui', $periodoFiscal)
                ->where('esta_procesado', true)->count();
            $registrosPendientes = $totalRegistros - $registrosProcesados;

            $registrosEnHistorial = RepBloqueo::query()->where('nro_liqui', $periodoFiscal)->count();
            $yaArchivado = $this->periodoYaArchivado($periodoFiscal);

            $estadisticasHistorial = $this->historialService->getEstadisticasHistorial($periodoFiscal);
            $estadisticasLimpieza = $this->cleanupService->contarRegistrosPorEstado($periodoFiscal);

            return [
                'periodo_fiscal' => $periodoFiscal,
                'estado_general' => [
                    'total_registros' => $totalRegistros,
                    'procesados' => $registrosProcesados,
                    'pendientes' => $registrosPendientes,
                    'en_historial' => $registrosEnHistorial,
                    'ya_archivado' => $yaArchivado,
                    'puede_archivar' => $this->validarArchivado($periodoFiscal),
                ],
                'estadisticas_historial' => $estadisticasHistorial,
                'estadisticas_trabajo' => $estadisticasLimpieza,
                'timestamp' => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::error('Error al obtener resumen de estado', [
                'periodo_fiscal' => $periodoFiscal,
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => true,
                'mensaje' => $e->getMessage(),
                'periodo_fiscal' => $periodoFiscal,
            ];
        }
    }

    /**
     * Verifica si un período ya fue archivado.
     */
    public function periodoYaArchivado(array $periodoFiscal): bool
    {
        try {
            // Un período se considera archivado si:
            // 1. Existen registros en el historial
            // 2. No existen registros procesados en la tabla de trabajo

            $registrosEnHistorial = RepBloqueo::query()->where('nro_liqui', $periodoFiscal)->count();
            $registrosProcesadosEnTrabajo = BloqueosDataModel::query()->where('nro_liqui', $periodoFiscal)
                ->where('esta_procesado', true)
                ->count();

            return $registrosEnHistorial > 0 && $registrosProcesadosEnTrabajo === 0;
        } catch (Exception $e) {
            Log::error('Error al verificar si período ya fue archivado', [
                'periodo_fiscal' => $periodoFiscal,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Obtiene los registros que están listos para ser procesados.
     */
    private function obtenerRegistrosParaProcesar(array $periodoFiscal): Collection
    {
        // Usar el servicio para obtener la liquidación definitiva del período
        $periodoFiscalService = resolve(PeriodoFiscalService::class);
        $liquidacion = $periodoFiscalService->getLiquidacionDefinitiva($periodoFiscal['year'], $periodoFiscal['month']);

        if (!$liquidacion) {
            // No hay liquidación definitiva para ese período
            return collect();
        }

        $nroLiqui = $liquidacion->nro_liqui;

        return BloqueosDataModel::query()->where('nro_liqui', $nroLiqui)
            ->where('esta_procesado', true)
            ->whereIn('estado', [
                BloqueosEstadoEnum::PROCESADO,
                BloqueosEstadoEnum::VALIDADO,
            ])
            ->get();
    }
}
