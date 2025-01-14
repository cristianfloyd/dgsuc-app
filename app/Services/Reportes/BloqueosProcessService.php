<?php

namespace App\Services\Reportes;

use Carbon\Carbon;
use App\Models\Dh03;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Data\Reportes\BloqueosData;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Reportes\BloqueosDataModel;
use App\Data\Reportes\BloqueoProcesadoData;
use App\Enums\BloqueosEstadoEnum;

class BloqueosProcessService
{
    use MapucheConnectionTrait;

    public function crearTablaBackupSiNoExiste(): void
    {
        if (!Schema::connection($this->getConnectionName())->hasTable('suc.dh03_backup_bloqueos')) {
            Schema::connection($this->getConnectionName())->create('suc.dh03_backup_bloqueos', function (Blueprint $table) {
                $table->id();
                $table->integer('nro_liqui');
                $table->integer('nro_cargo')->unique();
                $table->integer('nro_legaj');
                $table->date('fec_baja')->nullable();
                $table->date('fecha_baja_nueva')->nullable();
                $table->boolean('chkstopliq')->default(false);
                $table->string('tipo_bloqueo');
                $table->timestamp('fecha_backup');
                $table->string('session_id');

                $table->index('nro_cargo');
                $table->index('session_id');
            });
        }
    }

    /**
     * Procesa los bloqueos pendientes en el sistema.
     *
     * Este método se encarga de procesar los registros de bloqueos (licencias, bajas, etc.)
     * realizando las actualizaciones correspondientes en la tabla DH03 y manteniendo un
     * registro de backup para posible restauración.
     *
     * @param BloqueosData|null $bloqueosData Datos específicos a procesar. Si es null, procesa todos los registros pendientes.
     * @return Collection<BloqueoProcesadoData> Colección con los resultados del procesamiento
     * @throws \Exception Si ocurre un error durante el procesamiento
     */
    public function procesarBloqueos(?BloqueosData $bloqueosData = null): Collection
    {
        $resultados = new Collection();

        try {
            DB::connection($this->getConnectionName())->beginTransaction();

            // Aseguramos que existe la tabla de backup
            $this->crearTablaBackupSiNoExiste();

            // Definir el query base con validaciones de estado
            $query = BloqueosDataModel::query()
                ->where('estado', BloqueosEstadoEnum::VALIDADO)
                ->whereNull('mensaje_error');

            if ($bloqueosData !== null) {
                // Validar que todos los registros estén en estado correcto
                $registrosInvalidos = $bloqueosData->filter(function ($bloqueo) {
                    return $bloqueo->estado !== BloqueosEstadoEnum::VALIDADO ||
                           !is_null($bloqueo->mensaje_error);
                });

                if ($registrosInvalidos->isNotEmpty()) {
                    throw new \Exception(
                        'Existen registros no validados o con errores. ' .
                        'Por favor, valide todos los registros antes de procesar.'
                    );
                }

                $query->whereIn('id', $bloqueosData->pluck('id'));
            }

            // Verificar que existan registros para procesar
            if ($query->count() === 0) {
                throw new \Exception('No hay registros válidos para procesar.');
            }

            // Procesar en lotes
            $query->chunk(100, function ($lote) use (&$resultados) {
                // Preparar backup del lote actual
                $backup = $this->prepararBackupLote($lote);
                if ($backup->isNotEmpty()) {
                    DB::connection($this->getConnectionName())
                        ->table('suc.dh03_backup_bloqueos')
                        ->insert($backup->toArray());
                }

                // Procesar registros
                foreach ($lote as $bloqueo) {
                    try {
                        $resultado = $this->procesarRegistro($bloqueo);
                        $resultados->push($resultado->toResource());

                        if ($resultado->success) {
                            // Actualizar estado antes de eliminar
                            $bloqueo->update([
                                'estado' => BloqueosEstadoEnum::PROCESADO,
                                'mensaje_error' => null
                            ]);

                            $bloqueo->delete();

                            Log::info('Registro procesado y eliminado', [
                                'id' => $bloqueo->id,
                                'tipo' => $bloqueo->tipo,
                                'legajo' => $bloqueo->nro_legaj,
                                'cargo' => $bloqueo->nro_cargo
                            ]);
                        } else {
                            // Actualizar estado en caso de error
                            $bloqueo->update([
                                'estado' => BloqueosEstadoEnum::ERROR_PROCESAMIENTO,
                                'mensaje_error' => $resultado->error_message
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error procesando registro individual', [
                            'id' => $bloqueo->id,
                            'error' => $e->getMessage()
                        ]);

                        $bloqueo->update([
                            'estado' => BloqueosEstadoEnum::ERROR_PROCESAMIENTO,
                            'mensaje_error' => $e->getMessage()
                        ]);
                    }
                }
            });

            DB::connection($this->getConnectionName())->commit();

            Log::info('Procesamiento completado', [
                'total_procesados' => $resultados->count(),
                'exitosos' => $resultados->where('success', true)->count(),
                'fallidos' => $resultados->where('success', false)->count()
            ]);

            return $resultados;

        } catch (\Exception $e) {
            DB::connection($this->getConnectionName())->rollBack();
            Log::error('Error en procesamiento de bloqueos', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    public function procesarRegistro(BloqueosDataModel $bloqueo): BloqueoProcesadoData
    {
        try {
            // Validamos la existencia del par legajo-cargo
            Log::info('Validando existencia del par legajo-cargo');
            if (!Dh03::validarParLegajoCargo($bloqueo->nro_legaj, $bloqueo->nro_cargo)) {
                return BloqueoProcesadoData::fromError(
                    'Combinacion de legajo y cargo no encontrada',
                    $bloqueo,
                    ['error_tipo0' => 'validacion']
                );
            }

            $cargo = Dh03::validarLegajoCargo($bloqueo->nro_legaj, $bloqueo->nro_cargo)->first();



            // Procesar según tipo
            Log::info("Procesando tipo de bloqueo: {$bloqueo->tipo}");
            match ($bloqueo->tipo) {
                'licencia' => $this->procesarLicencia($cargo),
                'fallecido', 'renuncia' => $this->procesarBaja($cargo, $bloqueo->fecha_baja),
                default => throw new \Exception('Tipo de bloqueo no válido')
            };

            // Si llegamos aquí, el proceso fue exitoso
            $resultado = BloqueoProcesadoData::fromSuccess($bloqueo);

            Log::info('Proceso exitoso:', $resultado->toArray());
            return $resultado;

        } catch (\Exception $e) {
            // En caso de error, el registro permanece en la tabla
            return BloqueoProcesadoData::fromError(
                $e->getMessage(),
                $bloqueo,
                [
                    'error_tipo' => 'procesamiento',
                    'error_trace' => $e->getTraceAsString()
                ]
            );
        }
    }


    private function procesarLicencia(Dh03 $cargo): void
    {
        $cargo->update(['chkstopliq' => true]);
    }

    private function procesarBaja(Dh03 $cargo, string $fechaBaja): void
    {
        $fechaBajaImportada = Carbon::parse($fechaBaja);
        $fechaBajaDh03 = $cargo->fec_baja ? Carbon::parse($cargo->fec_baja) : null;

        if (!$fechaBajaDh03 || $fechaBajaImportada->lt($fechaBajaDh03)) {
            $cargo->update(['fec_baja' => $fechaBajaImportada]);
        }
    }

    public function prepararBackupLote(Collection $bloqueos): Collection
    {
        Log::info('Preparando backup del lote de bloqueos');
        $backup = $bloqueos->map(function ($bloqueo) {
            $cargo = Dh03::validarLegajoCargo($bloqueo->nro_legaj, $bloqueo->nro_cargo)->first();

            if (!$cargo) return null;

            return [
                'nro_cargo' => $cargo->nro_cargo,
                'nro_legaj' => $cargo->nro_legaj,
                'nro_liqui' => $bloqueo->nro_liqui,
                'fec_baja' => $cargo->fec_baja,
                'fecha_baja_nueva' => $bloqueo->fecha_baja,
                'chkstopliq' => $cargo->chkstopliq,
                'tipo_bloqueo' => $bloqueo->tipo,
                'fecha_backup' => now(),
                'session_id' => session()->getId()
            ];
        })->filter();
        Log::info('Backup preparado', ['backup' => $backup]);
        return $backup;
    }


    public function restaurarBackup(): bool
    {
        try {
            DB::connection($this->getConnectionName())->beginTransaction();

            // Obtener registros de backup para la sesión actual
            $backup = DB::connection($this->getConnectionName())->table('suc.dh03_backup_bloqueos')
                ->where('session_id', session()->getId())
                ->orderBy('fecha_backup', 'desc')
                ->get();

            // Restaurar cada registro y almacenar los IDs procesados
            $idsRestaurados = [];
            foreach ($backup as $registro) {
                Dh03::where('nro_cargo', $registro->nro_cargo)
                    ->update([
                        'fec_baja' => $registro->fec_baja,
                        'chkstopliq' => $registro->chkstopliq
                    ]);

                $idsRestaurados[] = $registro->id;
            }

            // Eliminar los registros de backup que fueron restaurados
            if (!empty($idsRestaurados)) {
                DB::connection($this->getConnectionName())
                    ->table('suc.dh03_backup_bloqueos')
                    ->whereIn('id', $idsRestaurados)
                    ->delete();
            }

            DB::connection($this->getConnectionName())->commit();

            return true;
        } catch (\Exception $e) {
            DB::connection($this->getConnectionName())->rollBack();
            throw $e;
        }
    }


    private function procesarLicencias()
    {
        BloqueosDataModel::where('tipo', 'Licencia')
            ->whereHas('cargo')
            ->chunk(100, function ($licencias) {
                foreach ($licencias as $licencia) {
                    $cargo = $licencia->cargo;
                    if ($cargo) {
                        $cargo->update(['chkstopliq' => true]);
                    }
                }
            });
    }

    private function procesarBajas()
    {
        BloqueosDataModel::whereIn('tipo', ['Fallecido', 'Renuncia'])
            ->whereHas('cargo')
            ->chunk(100, function ($bajas) {
                foreach ($bajas as $baja) {
                    $cargo = $baja->cargo;
                    if (!$cargo) continue;

                    $fechaBajaImportada = Carbon::parse($baja->fecha_baja);
                    $fechaBajaDh03 = $cargo->fec_baja ? Carbon::parse($cargo->fec_baja) : null;

                    if (!$fechaBajaDh03 || $fechaBajaImportada->lt($fechaBajaDh03)) {
                        $cargo->update(['fec_baja' => $fechaBajaImportada]);
                    }
                }
            });
    }
}
