<?php

namespace App\Services\Reportes;

use Carbon\Carbon;
use App\Models\Dh03;
use App\Enums\BloqueosEstadoEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Data\Reportes\BloqueosData;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Reportes\BloqueosDataModel;
use App\Data\Reportes\BloqueoProcesadoData;

class BloqueosProcessService
{
    use MapucheConnectionTrait;

    /**
     * Crea la tabla de backup para bloqueos si no existe.
     *
     * Este método verifica si la tabla 'suc.dh03_backup_bloqueos' existe en la conexión actual.
     * Si no existe, la crea con una estructura predefinida para almacenar información de respaldo
     * de bloqueos, incluyendo campos como número de liquidación, número de cargo, fecha de baja,
     * tipo de bloqueo, entre otros.
     *
     * @return void
     */
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
                ->whereNull('mensaje_error')
                ->where('esta_procesado', false); // Solo procesamos los que no están procesados

            if ($bloqueosData !== null) {
                // Validar que todos los registros estén en estado correcto
                $registrosInvalidos = collect($bloqueosData)->filter(function ($bloqueo) {
                    return $bloqueo->estado !== BloqueosEstadoEnum::VALIDADO ||
                        $bloqueo->mensaje_error !== null ||
                        $bloqueo->esta_procesado === true;
                });

                if ($registrosInvalidos->isNotEmpty()) {
                    throw new \Exception(
                        'Existen registros no validados, con errores o ya procesados. ' .
                            'Por favor, valide todos los registros antes de procesar.'
                    );
                }

                $query->whereIn('id', collect($bloqueosData)->pluck('id'));
            }

            // Verificar que existan registros para procesar
            if ($query->count() === 0) {
                throw new \Exception('No hay registros válidos para procesar.');
            }

            // Procesar en lotes
            $query->chunk(100, function ($lote) use (&$resultados) {
                // Procesamos cada registro individualmente
                foreach ($lote as $bloqueo) {
                    try {
                        // Obtenemos el cargo antes de cualquier modificación
                        $cargo = Dh03::validarLegajoCargo($bloqueo->nro_legaj, $bloqueo->nro_cargo)->first();

                        if ($cargo) {
                            // Procesamos el registro y verificamos si se realizaron cambios
                            $resultado = $this->procesarRegistro($bloqueo, $cargo);

                            // Si el procesamiento fue exitoso y se realizaron cambios, guardamos el backup
                            if ($resultado->success && $resultado->cambiosRealizados) {
                                // Crear backup solo para este registro específico
                                $backupData = $this->prepararBackupRegistro($bloqueo, $cargo, $resultado->datosOriginales);

                                DB::connection($this->getConnectionName())
                                    ->table('suc.dh03_backup_bloqueos')
                                    ->insert($backupData);
                            }

                            $resultados->push($resultado->toResource());

                            if ($resultado->success) {
                                // Actualizar estado y marcar como procesado en lugar de eliminar
                                $bloqueo->update([
                                    'estado' => BloqueosEstadoEnum::PROCESADO,
                                    'mensaje_error' => null,
                                    'esta_procesado' => true
                                ]);

                                Log::info('Registro procesado y marcado como procesado', [
                                    'id' => $bloqueo->id,
                                    'tipo' => $bloqueo->tipo,
                                    'legajo' => $bloqueo->nro_legaj,
                                    'cargo' => $bloqueo->nro_cargo,
                                    'cambios_realizados' => $resultado->cambiosRealizados
                                ]);
                            } else {
                                // Actualizar estado en caso de error
                                $bloqueo->update([
                                    'estado' => BloqueosEstadoEnum::ERROR_PROCESO,
                                    'mensaje_error' => $resultado->message
                                ]);
                            }
                        } else {
                            // ... existing code ...
                        }
                    } catch (\Exception $e) {
                        Log::error('Error procesando registro individual', [
                            'id' => $bloqueo->id,
                            'error' => $e->getMessage()
                        ]);

                        $bloqueo->update([
                            'estado' => BloqueosEstadoEnum::ERROR_PROCESO,
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

    public function procesarRegistro(BloqueosDataModel $bloqueo, ?Dh03 $cargo = null): BloqueoProcesadoData
    {
        try {
            // Validamos la existencia del par legajo-cargo
            Log::info('Validando existencia del par legajo-cargo');
            if (!$cargo && !Dh03::validarParLegajoCargo($bloqueo->nro_legaj, $bloqueo->nro_cargo)) {
                return BloqueoProcesadoData::fromError(
                    'Combinacion de legajo y cargo no encontrada',
                    $bloqueo,
                    ['error_tipo0' => 'validacion']
                );
            }

            if (!$cargo) {
                $cargo = Dh03::buscarPorLegajoCargo($bloqueo->nro_legaj, $bloqueo->nro_cargo)->first();
            }

            // Guardar datos originales antes de cualquier modificación
            $datosOriginales = [
                'fec_baja' => $cargo->fec_baja,
                'chkstopliq' => $cargo->chkstopliq
            ];

            // Procesar según tipo
            Log::info("Procesando tipo de bloqueo: {$bloqueo->tipo}");
            $cambiosRealizados = match ($bloqueo->tipo) {
                'licencia' => $this->procesarLicencia($cargo),
                'fallecido', 'renuncia' => $this->procesarBaja($cargo, $bloqueo->fecha_baja),
                default => throw new \Exception('Tipo de bloqueo no válido')
            };

            // Si llegamos aquí, el proceso fue exitoso
            $resultado = BloqueoProcesadoData::fromSuccess($bloqueo);
            $resultado->cambiosRealizados = $cambiosRealizados;
            $resultado->datosOriginales = $datosOriginales;

            Log::info('Proceso exitoso:', array_merge($resultado->toArray(), ['cambios_realizados' => $cambiosRealizados]));
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

    private function procesarLicencia(Dh03 $cargo): bool
    {
        // Si ya está bloqueado, no hacemos cambios
        if ($cargo->chkstopliq) {
            return false;
        }

        $cargo->update(['chkstopliq' => true]);
        return true;
    }

    private function procesarBaja(Dh03 $cargo, string $fechaBaja): bool
    {
        $fechaBajaImportada = Carbon::parse($fechaBaja);
        $fechaBajaDh03 = $cargo->fec_baja ? Carbon::parse($cargo->fec_baja) : null;

        if (!$fechaBajaDh03 || $fechaBajaImportada->lt($fechaBajaDh03)) {
            $cargo->update(['fec_baja' => $fechaBajaImportada]);
            return true;
        }

        return false;
    }

    /**
     * Prepara un backup para un registro específico.
     *
     * @param BloqueosDataModel $bloqueo El bloqueo que se está procesando
     * @param Dh03 $cargo El cargo asociado al bloqueo
     * @param array $datosOriginales Los datos originales antes de la modificación
     * @return array Datos preparados para el backup
     */
    public function prepararBackupRegistro(BloqueosDataModel $bloqueo, Dh03 $cargo, array $datosOriginales): array
    {
        Log::info('Preparando backup para registro individual', [
            'cargo' => $cargo->nro_cargo,
            'legajo' => $cargo->nro_legaj,
            'datos_originales' => $datosOriginales
        ]);

        return [
            'nro_cargo' => $cargo->nro_cargo,
            'nro_legaj' => $cargo->nro_legaj,
            'nro_liqui' => $bloqueo->nro_liqui,
            'fec_baja' => $datosOriginales['fec_baja'],
            'fecha_baja_nueva' => $bloqueo->fecha_baja,
            'chkstopliq' => $datosOriginales['chkstopliq'],
            'tipo_bloqueo' => $bloqueo->tipo,
            'fecha_backup' => now(),
            'session_id' => session()->getId()
        ];
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

    public function procesarBloqueosDuplicados(): bool
    {
        try {
            DB::connection($this->getConnectionName())->beginTransaction();

            // Aseguramos que existe la tabla de backup
            $this->crearTablaBackupSiNoExiste();

            // Identificar grupos de registros con el mismo nro_legaj y nro_cargo
            $duplicados = BloqueosDataModel::select('nro_legaj', 'nro_cargo', DB::raw('COUNT(*) as total_count'))
                ->where('esta_procesado', false) // Solo procesamos los que no están procesados
                ->groupBy('nro_legaj', 'nro_cargo')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            $procesados = 0;

            foreach ($duplicados as $grupo) {
                // Obtener todos los registros duplicados para este par legajo-cargo
                $registrosDuplicados = BloqueosDataModel::where('nro_legaj', $grupo->nro_legaj)
                    ->where('nro_cargo', $grupo->nro_cargo)
                    ->where('esta_procesado', false) // Solo procesamos los que no están procesados
                    ->orderBy('created_at', 'asc') // Procesamos primero los más antiguos
                    ->get();

                // Verificar si el cargo existe en Mapuche
                if (Dh03::validarParLegajoCargo($grupo->nro_legaj, $grupo->nro_cargo)) {
                    $cargo = Dh03::validarLegajoCargo($grupo->nro_legaj, $grupo->nro_cargo)->first();

                    // Guardar datos originales antes de cualquier modificación
                    $datosOriginales = [
                        'fec_baja' => $cargo->fec_baja,
                        'chkstopliq' => $cargo->chkstopliq
                    ];

                    // Procesar solo el primer registro (el más antiguo)
                    $primerRegistro = $registrosDuplicados->shift();
                    $resultado = $this->procesarRegistro($primerRegistro, $cargo);

                    if ($resultado->success && $resultado->cambiosRealizados) {
                        // Crear backup solo si se realizaron cambios
                        $backupData = $this->prepararBackupRegistro($primerRegistro, $cargo, $datosOriginales);

                        DB::connection($this->getConnectionName())
                            ->table('suc.dh03_backup_bloqueos')
                            ->insert($backupData);

                        Log::info('Backup creado para registro duplicado', [
                            'cargo' => $cargo->nro_cargo,
                            'legajo' => $cargo->nro_legaj
                        ]);
                    }

                    if ($resultado->success) {
                        $this->marcarComoProcesado($primerRegistro);
                        $procesados++;
                    }

                    // Marcar el resto como duplicados y procesados
                    foreach ($registrosDuplicados as $duplicado) {
                        $duplicado->update([
                            'estado' => BloqueosEstadoEnum::DUPLICADO,
                            'mensaje_error' => 'Registro duplicado. Se procesó el registro más antiguo.',
                            'esta_procesado' => true
                        ]);

                        $procesados++;
                    }
                }
            }

            DB::connection($this->getConnectionName())->commit();

            Log::info("Procesamiento de duplicados completado", [
                'total_procesados' => $procesados
            ]);

            return true;
        } catch (\Exception $e) {
            DB::connection($this->getConnectionName())->rollBack();
            Log::error('Error procesando duplicados', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Marca un registro como procesado.
     *
     * @param BloqueosDataModel $registro El registro a marcar como procesado
     * @return void
     */
    private function marcarComoProcesado(BloqueosDataModel $registro): void
    {
        $registro->update([
            'estado' => BloqueosEstadoEnum::PROCESADO,
            'mensaje_error' => null,
            'esta_procesado' => true
        ]);

        Log::info('Registro marcado como procesado', [
            'id' => $registro->id,
            'tipo' => $registro->tipo,
            'legajo' => $registro->nro_legaj,
            'cargo' => $registro->nro_cargo
        ]);
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
