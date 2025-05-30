<?php

namespace App\Services\Reportes;

use Exception;
use App\Enums\BloqueosEstadoEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use App\Data\Reportes\CleanupResultData;
use App\Models\Reportes\BloqueosDataModel;
use App\Models\Mapuche\Bloqueos\RepBloqueo;
use App\Services\Reportes\Interfaces\BloqueosCleanupServiceInterface;

/**
 * Servicio para limpiar la tabla de trabajo de bloqueos
 *
 * Responsabilidad: Eliminar registros ya transferidos al historial de BloqueosDataModel
 */
class BloqueosCleanupService implements BloqueosCleanupServiceInterface
{
    use MapucheConnectionTrait;

    /**
     * Limpia la tabla de trabajo eliminando registros ya transferidos al historial
     */
    public function limpiarTablaWork(array $periodoFiscal): CleanupResultData
    {
        $startTime = microtime(true);
        $eliminados = 0;
        $noEliminados = 0;
        $idsEliminados = [];
        $idsNoEliminados = [];
        $periodoString = $periodoFiscal['year'] . '-' . str_pad($periodoFiscal['month'], 2, '0', STR_PAD_LEFT);

        Log::info('Iniciando limpieza de tabla de trabajo', [
            'periodo_fiscal' => $periodoFiscal
        ]);

        try {
            // Validar antes de procesar
            if (!$this->validarLimpieza($periodoFiscal)) {
                return CleanupResultData::error(
                    'No se puede realizar la limpieza de forma segura',
                    $periodoFiscal
                );
            }

            // Obtener estadísticas antes de la limpieza
            $estadisticasAntes = $this->contarRegistrosPorEstado($periodoFiscal);

            // Obtener registros listos para eliminar
            $registrosParaEliminar = $this->getRegistrosListosParaEliminar($periodoFiscal);

            if ($registrosParaEliminar->isEmpty()) {
                Log::info('No hay registros para limpiar', [
                    'periodo_fiscal' => $periodoString
                ]);
                return CleanupResultData::nothingToClean($periodoFiscal);
            }

            DB::connection($this->getConnectionName())->transaction(function () use (
                $registrosParaEliminar, &$eliminados, &$noEliminados, &$idsEliminados, &$idsNoEliminados
            ) {
                foreach ($registrosParaEliminar as $registro) {
                    try {
                        // Verificar una vez más que existe en el historial
                        if (!$this->existeEnHistorial($registro)) {
                            Log::warning('Registro no encontrado en historial, no se eliminará', [
                                'id' => $registro->id,
                                'legajo' => $registro->nro_legaj,
                                'cargo' => $registro->nro_cargo
                            ]);
                            $idsNoEliminados[] = [
                                'id' => $registro->id,
                                'razon' => 'No existe en el historial'
                            ];
                            $noEliminados++;
                            continue;
                        }

                        // Eliminar el registro
                        $registro->delete();
                        $idsEliminados[] = $registro->id;
                        $eliminados++;

                        Log::debug('Registro eliminado de tabla de trabajo', [
                            'id' => $registro->id,
                            'legajo' => $registro->nro_legaj,
                            'cargo' => $registro->nro_cargo
                        ]);

                    } catch (Exception $e) {
                        Log::error('Error al eliminar registro individual', [
                            'id' => $registro->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);

                        $idsNoEliminados[] = [
                            'id' => $registro->id,
                            'razon' => $e->getMessage()
                        ];
                        $noEliminados++;
                    }
                }
            });

            // Obtener estadísticas después de la limpieza
            $estadisticasDespues = $this->contarRegistrosPorEstado($periodoFiscal);

            $endTime = microtime(true);
            $duration = $endTime - $startTime;

            Log::info('Limpieza de tabla de trabajo completada', [
                'eliminados' => $eliminados,
                'no_eliminados' => $noEliminados,
                'duracion_segundos' => $duration,
                'periodo_fiscal' => $periodoString
            ]);

            // Retornar resultado apropiado
            if ($noEliminados === 0) {
                return CleanupResultData::success(
                    $eliminados,
                    $periodoFiscal,
                    $idsEliminados,
                    $estadisticasAntes,
                    $estadisticasDespues
                );
            } else {
                return CleanupResultData::partial(
                    $eliminados,
                    $noEliminados,
                    $periodoFiscal,
                    $idsEliminados,
                    $idsNoEliminados
                );
            }

        } catch (Exception $e) {
            Log::error('Error general en limpieza de tabla de trabajo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'periodo_fiscal' => $periodoFiscal
            ]);

            return CleanupResultData::error(
                "Error en la limpieza: {$e->getMessage()}",
                $periodoFiscal
            );
        }
    }

    /**
     * Valida que se pueda realizar la limpieza de forma segura
     */
    public function validarLimpieza(array $periodoFiscal): bool
    {
        $periodoString = $periodoFiscal['year'] . '-' . str_pad($periodoFiscal['month'], 2, '0', STR_PAD_LEFT);
        try {
            // Obtener nro_liqui definitivo
            $periodoService = app(\App\Services\Mapuche\PeriodoFiscalService::class);
            $liquidacion = $periodoService->getLiquidacionDefinitiva($periodoFiscal['year'], $periodoFiscal['month']);
            if (!$liquidacion) {
                Log::info('No existe liquidación definitiva para el período fiscal', [
                    'periodo_fiscal' => $periodoString
                ]);
                return false;
            }
            $nroLiqui = $liquidacion->nro_liqui;

            // Verificar que existan registros para el período
            $totalRegistros = BloqueosDataModel::where('nro_liqui', $nroLiqui)->count();
            if ($totalRegistros === 0) {
                Log::info('No existen registros para el período fiscal', [
                    'periodo_fiscal' => $periodoString
                ]);
                return true; // No hay nada que limpiar, es válido
            }

            // Verificar que no haya registros pendientes críticos
            $registrosPendientes = $this->getRegistrosPendientes($periodoFiscal);
            if ($registrosPendientes->isNotEmpty()) {
                Log::warning('Existen registros pendientes que no pueden ser eliminados', [
                    'periodo_fiscal' => $periodoString,
                    'cantidad_pendientes' => $registrosPendientes->count(),
                    'ids_pendientes' => $registrosPendientes->pluck('id')->toArray()
                ]);
                // Permitir limpieza parcial, solo advertir
            }

            // Verificar que exista el historial correspondiente
            $registrosEnHistorial = RepBloqueo::where('nro_liqui', $nroLiqui)->count();
            if ($registrosEnHistorial === 0) {
                Log::error('No existen registros en el historial para este período', [
                    'periodo_fiscal' => $periodoString
                ]);
                return false;
            }

            return true;

        } catch (Exception $e) {
            Log::error('Error al validar limpieza', [
                'periodo_fiscal' => $periodoString,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obtiene registros pendientes que no pueden ser eliminados
     */
    public function getRegistrosPendientes(?array $periodoFiscal = null): Collection
    {
        $query = BloqueosDataModel::query();
        $nroLiqui = null;
        if ($periodoFiscal) {
            $periodoService = app(\App\Services\Mapuche\PeriodoFiscalService::class);
            $liquidacion = $periodoService->getLiquidacionDefinitiva($periodoFiscal['year'], $periodoFiscal['month']);
            if ($liquidacion) {
                $nroLiqui = $liquidacion->nro_liqui;
                $query->where('nro_liqui', $nroLiqui);
            }
        }
        // Registros que no están procesados o tienen errores críticos
        return $query->where(function ($q) {
            $q->where('esta_procesado', false)
              ->orWhereIn('estado', [
                  BloqueosEstadoEnum::ERROR_VALIDACION,
                  BloqueosEstadoEnum::ERROR_PROCESO,
                  BloqueosEstadoEnum::FALTA_CARGO_ASOCIADO
              ]);
        })->get();
    }

    /**
     * Obtiene registros listos para ser eliminados
     */
    public function getRegistrosListosParaEliminar(array $periodoFiscal): Collection
    {
        $periodoService = app(\App\Services\Mapuche\PeriodoFiscalService::class);
        $liquidacion = $periodoService->getLiquidacionDefinitiva($periodoFiscal['year'], $periodoFiscal['month']);
        if (!$liquidacion) {
            return collect();
        }
        $nroLiqui = $liquidacion->nro_liqui;
        return BloqueosDataModel::where('nro_liqui', $nroLiqui)
            ->where('esta_procesado', true)
            ->whereIn('estado', [
                BloqueosEstadoEnum::PROCESADO,
                BloqueosEstadoEnum::VALIDADO
            ])
            ->get()
            ->filter(function ($registro) {
                // Verificar que existe en el historial antes de marcarlo para eliminar
                return $this->existeEnHistorial($registro);
            });
    }

    /**
     * Cuenta registros por estado para un período
     */
    public function contarRegistrosPorEstado(array $periodoFiscal): array
    {
        $periodoService = app(\App\Services\Mapuche\PeriodoFiscalService::class);
        $liquidacion = $periodoService->getLiquidacionDefinitiva($periodoFiscal['year'], $periodoFiscal['month']);
        $periodoString = $periodoFiscal['year'] . '-' . str_pad($periodoFiscal['month'], 2, '0', STR_PAD_LEFT);
        if (!$liquidacion) {
            return [
                'error' => true,
                'mensaje' => 'No existe liquidación definitiva para el período fiscal',
                'periodo_fiscal' => $periodoString
            ];
        }
        $nroLiqui = $liquidacion->nro_liqui;
        try {
            $conteos = BloqueosDataModel::where('nro_liqui', $nroLiqui)
                ->selectRaw('estado, COUNT(*) as total')
                ->groupBy('estado')
                ->pluck('total', 'estado')
                ->toArray();

            // Convertir enum values a strings para el array
            $resultado = [];
            foreach ($conteos as $estado => $total) {
                $estadoEnum = BloqueosEstadoEnum::from($estado);
                $resultado[$estadoEnum->value] = $total;
            }

            return [
                'por_estado' => $resultado,
                'total_registros' => array_sum($resultado),
                'procesados' => BloqueosDataModel::where('nro_liqui', $nroLiqui)
                    ->where('esta_procesado', true)->count(),
                'pendientes' => BloqueosDataModel::where('nro_liqui', $nroLiqui)
                    ->where('esta_procesado', false)->count(),
                'periodo_fiscal' => $periodoString,
                'timestamp' => now()->toISOString()
            ];

        } catch (Exception $e) {
            Log::error('Error al contar registros por estado', [
                'periodo_fiscal' => $periodoString,
                'error' => $e->getMessage()
            ]);

            return [
                'error' => true,
                'mensaje' => $e->getMessage(),
                'periodo_fiscal' => $periodoString
            ];
        }
    }

    /**
     * Verifica si un registro existe en el historial
     */
    private function existeEnHistorial(BloqueosDataModel $registro): bool
    {
        return RepBloqueo::where('nro_liqui', $registro->nro_liqui)
            ->where('nro_legaj', $registro->nro_legaj)
            ->where('nro_cargo', $registro->nro_cargo)
            ->exists();
    }
}
