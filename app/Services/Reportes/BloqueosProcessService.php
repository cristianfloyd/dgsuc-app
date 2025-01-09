<?php

namespace App\Services\Reportes;

use Carbon\Carbon;
use App\Models\Dh03;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Reportes\BloqueosDataModel;
use App\Data\Reportes\BloqueoProcesadoData;

class BloqueosProcessService
{
    use MapucheConnectionTrait;

    public function crearTablaBackupSiNoExiste(): void
    {
        if (!Schema::connection($this->getConnectionName())->hasTable('suc.dh03_backup_bloqueos')) {
            Schema::connection($this->getConnectionName())->create('suc.dh03_backup_bloqueos', function (Blueprint $table) {
                $table->id();
                $table->integer('nro_cargo')->unique();
                $table->date('fec_baja')->nullable();
                $table->date('fecha_baja_nueva')->nullable();
                $table->boolean('chkstopliq')->default(false);
                $table->timestamp('fecha_backup');
                $table->string('session_id');

                $table->index('nro_cargo');
                $table->index('session_id');
            });
        }
    }

    public function procesarBloqueos(): Collection
    {
        $resultados = new Collection();

        try {
            DB::connection($this->getConnectionName())->beginTransaction();

            // Aseguramos que existe la tabla de backup
            $this->crearTablaBackupSiNoExiste();

            // Crear backup antes de procesar
            $backup = $this->crearBackup();
            DB::connection($this->getConnectionName())->table('suc.dh03_backup_bloqueos')->insert($backup->toArray());


            // Procesar registros
            BloqueosDataModel::query()
                ->chunk(100, function ($bloqueos) use (&$resultados) {
                    foreach ($bloqueos as $bloqueo) {
                        $resultado = $this->procesarRegistro($bloqueo);
                        $resultados->push($resultado->toResource());

                        // Si el proceso fue exitoso, eliminar el registro de la tabla import
                        if ($resultado->success) {
                            $bloqueo->delete();
                        }
                    }
                });

            DB::connection($this->getConnectionName())->commit();
            return $resultados;

        } catch (\Exception $e) {
            DB::connection($this->getConnectionName())->rollBack();
            throw $e;
        }
    }

    public function procesarRegistro(BloqueosDataModel $bloqueo): BloqueoProcesadoData
    {
        try {
            $cargo = Dh03::where('nro_cargo', $bloqueo->nro_cargo)
                        ->where('nro_legaj', $bloqueo->nro_legaj)
                        ->first();

            if (!$cargo) {
                return new BloqueoProcesadoData(
                    success: false,
                    message: "Cargo no encontrado",
                    bloqueo: $bloqueo,
                    processed_at: now(),
                    metadata: ['error' => 'Cargo inexistente']
                );
            }

            match ($bloqueo->tipo) {
                'Licencia' => $this->procesarLicencia($cargo),
                'Fallecido', 'Renuncia' => $this->procesarBaja($cargo, $bloqueo->fecha_baja),
                default => throw new \Exception('Tipo de bloqueo no válido')
            };

            return BloqueoProcesadoData::fromModel($bloqueo);

        } catch (\Exception $e) {
            return new BloqueoProcesadoData(
                success: false,
                message: $e->getMessage(),
                bloqueo: $bloqueo,
                processed_at: now(),
                metadata: ['error_trace' => $e->getTraceAsString()]
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

    public function crearBackup(): Collection
    {
        return DB::connection($this->getConnectionName())->table('dh03')
            ->whereIn('nro_cargo', function($query) {
                $query->select('nro_cargo')
                    ->from('suc.rep_bloqueos_import');
            })
            ->get()
            ->map(function($item) {
                // Obtenemos la fecha_baja de la tabla de importación
                $bloqueoImportado = DB::connection($this->getConnectionName())
                    ->table('suc.rep_bloqueos_import')
                    ->where('nro_cargo', $item->nro_cargo)
                    ->first();


                return [
                    'nro_cargo' => $item->nro_cargo,
                    'fec_baja' => $item->fec_baja,
                    'fecha_baja_nueva' => $bloqueoImportado->fecha_baja,
                    'chkstopliq' => $item->chkstopliq,
                    'fecha_backup' => now(),
                    'session_id' => session()->getId()
                ];
            });
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
