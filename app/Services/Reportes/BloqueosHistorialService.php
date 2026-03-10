<?php

namespace App\Services\Reportes;

use App\Data\Reportes\TransferResultData;
use App\Enums\BloqueosEstadoEnum;
use App\Models\Mapuche\Bloqueos\RepBloqueo;
use App\Models\Reportes\BloqueosDataModel;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Services\Reportes\Interfaces\BloqueosHistorialServiceInterface;
use App\Traits\MapucheConnectionTrait;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function in_array;

/**
 * Servicio para transferir bloqueos procesados al historial.
 *
 * Responsabilidad: Mover registros de BloqueosDataModel a RepBloqueo
 */
class BloqueosHistorialService implements BloqueosHistorialServiceInterface
{
    use MapucheConnectionTrait;

    /**
     * Transfiere bloqueos procesados al historial.
     */
    public function transferirAlHistorial(Collection $bloqueos): TransferResultData
    {
        $startTime = microtime(true);
        $transferidos = 0;
        $fallidos = 0;
        $idsTransferidos = [];
        $idsFallidos = [];
        $periodoFiscal = $this->extraerPeriodoFiscal($bloqueos);
        $this->getNroLiquiFromPeriodoFiscal($periodoFiscal);

        Log::info('Iniciando transferencia al historial', [
            'total_registros' => $bloqueos->count(),
            'periodo_fiscal' => $periodoFiscal,
        ]);

        try {
            // Validar antes de procesar
            if (!$this->validarTransferencia($bloqueos)) {
                return TransferResultData::error(
                    'Los bloqueos no cumplen los requisitos para ser transferidos al historial',
                    $periodoFiscal,
                );
            }

            DB::connection($this->getConnectionName())->transaction(function () use (
                $bloqueos,
                &$transferidos,
                &$fallidos,
                &$idsTransferidos,
                &$idsFallidos
            ): void {
                foreach ($bloqueos as $bloqueo) {
                    try {
                        // Verificar que no exista ya en el historial
                        if ($this->existeEnHistorial($bloqueo)) {
                            Log::warning('Bloqueo ya existe en historial', [
                                'id' => $bloqueo->id,
                                'legajo' => $bloqueo->nro_legaj,
                                'cargo' => $bloqueo->nro_cargo,
                            ]);
                            $idsFallidos[] = [
                                'id' => $bloqueo->id,
                                'error' => 'Ya existe en el historial',
                            ];
                            $fallidos++;
                            continue;
                        }

                        // Crear registro en RepBloqueo
                        $historialData = $this->prepararDatosHistorial($bloqueo);
                        RepBloqueo::query()->create($historialData);

                        $idsTransferidos[] = $bloqueo->id;
                        $transferidos++;

                        Log::debug('Bloqueo transferido al historial', [
                            'id' => $bloqueo->id,
                            'legajo' => $bloqueo->nro_legaj,
                            'cargo' => $bloqueo->nro_cargo,
                        ]);
                    } catch (Exception $e) {
                        Log::error('Error al transferir bloqueo individual', [
                            'id' => $bloqueo->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);

                        $idsFallidos[] = [
                            'id' => $bloqueo->id,
                            'error' => $e->getMessage(),
                        ];
                        $fallidos++;
                    }
                }
            });

            $endTime = microtime(true);
            $duration = $endTime - $startTime;

            Log::info('Transferencia al historial completada', [
                'transferidos' => $transferidos,
                'fallidos' => $fallidos,
                'duracion_segundos' => $duration,
                'periodo_fiscal' => $periodoFiscal,
            ]);

            // Retornar resultado apropiado
            if ($fallidos === 0) {
                return TransferResultData::success(
                    $transferidos,
                    $periodoFiscal,
                    $idsTransferidos,
                    ['duracion_segundos' => $duration],
                );
            }
            return TransferResultData::partial(
                $transferidos,
                $fallidos,
                $periodoFiscal,
                $idsTransferidos,
                $idsFallidos,
            );
        } catch (Exception $e) {
            Log::error('Error general en transferencia al historial', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'periodo_fiscal' => $periodoFiscal,
            ]);

            return TransferResultData::error(
                "Error en la transferencia: {$e->getMessage()}",
                $periodoFiscal,
            );
        }
    }

    /**
     * Valida que los bloqueos puedan ser transferidos.
     */
    public function validarTransferencia(Collection $bloqueos): bool
    {
        if ($bloqueos->isEmpty()) {
            Log::warning('No hay bloqueos para transferir');
            return false;
        }

        // Verificar que todos estén procesados
        $noProceados = $bloqueos->reject(fn($bloqueo): bool => (bool) $bloqueo->esta_procesado);
        if ($noProceados->isNotEmpty()) {
            Log::error('Existen bloqueos no procesados', [
                'ids_no_procesados' => $noProceados->pluck('id')->toArray(),
            ]);
            return false;
        }

        // Verificar que todos estén en estado válido para transferir
        $estadosValidos = [
            BloqueosEstadoEnum::PROCESADO,
            BloqueosEstadoEnum::VALIDADO,
        ];

        $estadosInvalidos = $bloqueos->reject(
            fn($bloqueo): bool => in_array($bloqueo->estado, $estadosValidos),
        );

        if ($estadosInvalidos->isNotEmpty()) {
            Log::error('Existen bloqueos con estados inválidos para transferir', [
                'ids_estados_invalidos' => $estadosInvalidos->map(fn($b): array => [
                    'id' => $b->id,
                    'estado' => $b->estado->value,
                ])->all(),
            ]);
            return false;
        }

        return true;
    }

    /**
     * Obtiene estadísticas del historial para un período fiscal.
     */
    public function getEstadisticasHistorial(array $periodoFiscal): array
    {
        $nroLiqui = $this->getNroLiquiFromPeriodoFiscal($periodoFiscal);
        try {
            $query = RepBloqueo::query()->where('nro_liqui', $nroLiqui);

            return [
                'total_registros' => $query->count(),
                'por_tipo' => $query->selectRaw('tipo, COUNT(*) as total')
                    ->groupBy('tipo')
                    ->pluck('total', 'tipo')
                    ->toArray(),
                'fecha_primer_registro' => $query->min('created_at'),
                'fecha_ultimo_registro' => $query->max('created_at'),
                'periodo_fiscal' => $periodoFiscal,
            ];
        } catch (Exception $e) {
            Log::error('Error al obtener estadísticas del historial', [
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
     * Obtiene bloqueos ya transferidos al historial por período.
     */
    public function getBloqueosEnHistorial(array $periodoFiscal): Collection
    {
        $nroLiqui = $this->getNroLiquiFromPeriodoFiscal($periodoFiscal);
        try {
            return RepBloqueo::query()->where('nro_liqui', $nroLiqui)->latest()
                ->get();
        } catch (Exception $e) {
            Log::error('Error al obtener bloqueos del historial', [
                'periodo_fiscal' => $periodoFiscal,
                'error' => $e->getMessage(),
            ]);

            return collect();
        }
    }

    /**
     * Verifica si un bloqueo ya existe en el historial.
     */
    private function existeEnHistorial(BloqueosDataModel $bloqueo): bool
    {
        return RepBloqueo::query()->where('nro_liqui', $bloqueo->nro_liqui)
            ->where('nro_legaj', $bloqueo->nro_legaj)
            ->where('nro_cargo', $bloqueo->nro_cargo)
            ->exists();
    }

    /**
     * Prepara los datos para insertar en el historial.
     */
    private function prepararDatosHistorial(BloqueosDataModel $bloqueo): array
    {
        return [
            'nro_liqui' => $bloqueo->nro_liqui,
            'fecha_registro' => $bloqueo->fecha_registro,
            'email' => $bloqueo->email,
            'nombre' => $bloqueo->nombre,
            'usuario_mapuche' => $bloqueo->usuario_mapuche,
            'dependencia' => $bloqueo->dependencia,
            'nro_legaj' => $bloqueo->nro_legaj,
            'nro_cargo' => $bloqueo->nro_cargo,
            'fecha_baja' => $bloqueo->fecha_baja,
            'tipo' => $bloqueo->tipo,
            'observaciones' => $bloqueo->observaciones,
            'chkstopliq' => $bloqueo->chkstopliq,
            'estado' => $bloqueo->estado->value,
            'mensaje_error' => $bloqueo->mensaje_error,
            'datos_validacion' => [
                'tiene_cargo_asociado' => $bloqueo->tiene_cargo_asociado,
                'esta_procesado' => $bloqueo->esta_procesado,
                'fecha_transferencia' => now(),
                'transferido_por' => Auth::id() ?? 'sistema',
            ],
            'fecha_procesamiento' => now(),
            'procesado_por' => Auth::id() ?? 'sistema',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Extrae el período fiscal de una colección de bloqueos.
     */
    private function extraerPeriodoFiscal(Collection $bloqueos): array
    {
        $periodoFiscalService = resolve(PeriodoFiscalService::class);
        if ($bloqueos->isEmpty()) {
            return $periodoFiscalService->getPeriodoFiscal();
        }

        // Tomar el nro_liqui del primer registro y obtener el período fiscal real
        $primerBloqueo = $bloqueos->first();
        $nroLiqui = (int) $primerBloqueo->nro_liqui;
        $periodo = $this->getPeriodoFiscalFromNroLiqui($nroLiqui);
        return $periodo ?? $periodoFiscalService->getPeriodoFiscal();
    }

    /**
     * Obtiene el número de liquidación definitiva para un período fiscal.
     *
     * @param array $periodoFiscal Período fiscal en formato ['year' => int, 'month' => int]
     *
     * @return int|null Número de liquidación o null si no existe liquidación definitiva
     */
    private function getNroLiquiFromPeriodoFiscal(array $periodoFiscal): ?int
    {
        $periodoFiscalService = resolve(PeriodoFiscalService::class);
        $liquidacion = $periodoFiscalService->getLiquidacionDefinitiva($periodoFiscal['year'], $periodoFiscal['month']);
        return $liquidacion?->nro_liqui;
    }

    /**
     * Obtiene el período fiscal (['year' => ..., 'month' => ...]) a partir de un nro_liqui usando PeriodoFiscalService.
     *
     * @param int $nroLiqui Número de liquidación
     *
     * @return array|null Período fiscal ['year' => int, 'month' => int] o null si no existe
     */
    private function getPeriodoFiscalFromNroLiqui(int $nroLiqui): ?array
    {
        $periodoFiscalService = resolve(PeriodoFiscalService::class);
        $periodo = $periodoFiscalService->getPeriodoFiscalFromLiqui($nroLiqui);
        if (!$periodo || !isset($periodo['year'], $periodo['month'])) {
            return null;
        }
        return [
            'year' => (int) $periodo['year'],
            'month' => (int) $periodo['month'],
        ];
    }
}
