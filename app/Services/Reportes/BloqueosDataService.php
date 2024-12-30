<?php

namespace App\Services\Reportes;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Reportes\BloqueosDataModel;
use Illuminate\Database\Eloquent\Collection;

class BloqueosDataService
{
    /**
     * Obtiene todos los registros de bloqueos
     */
    public function getAllBloqueos(): Collection
    {
        try {
            return BloqueosDataModel::all();
        } catch (Exception $e) {
            Log::error('Error al obtener bloqueos: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene un bloqueo por su ID
     */
    public function getBloqueoById(int $id): ?BloqueosDataModel
    {
        try {
            return BloqueosDataModel::findOrFail($id);
        } catch (Exception $e) {
            Log::error("Error al obtener bloqueo ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Crea un nuevo registro de bloqueo
     */
    public function createBloqueo(array $data): BloqueosDataModel
    {
        try {
            return BloqueosDataModel::create($data);
        } catch (Exception $e) {
            Log::error('Error al crear bloqueo: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualiza un registro de bloqueo existente
     */
    public function updateBloqueo(int $id, array $data): BloqueosDataModel
    {
        try {
            $bloqueo = BloqueosDataModel::findOrFail($id);
            $bloqueo->update($data);
            return $bloqueo;
        } catch (Exception $e) {
            Log::error("Error al actualizar bloqueo ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Elimina un registro de bloqueo
     */
    public function deleteBloqueo(int $id): bool
    {
        try {
            $bloqueo = BloqueosDataModel::findOrFail($id);
            return $bloqueo->delete();
        } catch (Exception $e) {
            Log::error("Error al eliminar bloqueo ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Vacía completamente la tabla de bloqueos
     */
    public function truncateTable(): void
    {
        try {
            BloqueosDataModel::truncate();
        } catch (Exception $e) {
            Log::error('Error al vaciar tabla de bloqueos: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene bloqueos por número de legajo
     */
    public function getBloqueosByLegajo(int $nroLegajo): Collection
    {
        try {
            return BloqueosDataModel::where('nro_legaj', $nroLegajo)->get();
        } catch (Exception $e) {
            Log::error("Error al obtener bloqueos para legajo {$nroLegajo}: " . $e->getMessage());
            throw $e;
        }
    }
}
