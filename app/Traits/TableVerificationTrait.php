<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait TableVerificationTrait
{
    protected function verifyAndInitializeTable(string $tableServiceClass): bool
    {
        try {
            $tableService = app($tableServiceClass);

            if (!$tableService->exists()) {
                $tableService->createAndPopulate();
                Log::info("Tabla {$tableService->getTableName()} inicializada correctamente");
                return true;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error al inicializar tabla: " . $e->getMessage());
            return false;
        }
    }
}
