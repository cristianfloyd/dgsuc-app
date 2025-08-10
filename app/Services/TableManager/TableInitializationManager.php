<?php

namespace App\Services\TableManager;

use App\Contracts\TableService\TableServiceInterface;
use Illuminate\Support\Facades\Log;

/**
 * Administrador central para la inicialización de tablas.
 *
 * @package App\Services\TableManager
 */
class TableInitializationManager
{
    public function isTableInitialized(TableServiceInterface $service): bool
    {
        return $service->exists();
    }

    /**
     * Inicializa una tabla si no existe.
     *
     * @param TableServiceInterface $tableService
     *
     * @return bool
     */
    public function initializeTable(TableServiceInterface $tableService): bool
    {
        try {
            if (!$tableService->exists()) {
                $tableService->createTable();
                Log::info("Tabla {$tableService->getTableName()} inicializada correctamente");
                return true;
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Error al inicializar tabla: ' . $e->getMessage());
            return false;
        }
    }
}
