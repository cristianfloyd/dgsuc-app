<?php

namespace App\Services\Reportes;

use Illuminate\Support\Facades\Log;

class BloqueosImportOrchestratorService
{
    public function ejecutarSecuenciaCompleta(bool $procesarTodo = false, bool $validarTodos = true, bool $validarCargosAsociados = true): array
    {
        $resultados = [];

        try {
            if ($validarTodos) {
                // 1. Validar todos los registros
                $resultados['validar_todos'] = app(BloqueosValidationService::class)->validarTodosLosRegistros();
            }

            if ($validarCargosAsociados) {
                // 2. Validar cargos asociados
                $resultados['validar_cargos_asociados'] = app(ValidacionCargoAsociadoService::class)->validarCargosAsociados();
            }

            if ($procesarTodo) {
                // 3. Procesar bloqueos
                $resultados['procesar'] = app(BloqueosProcessService::class)->procesarBloqueos();

                // 4. Procesar duplicados
                $resultados['procesar_duplicados'] = app(BloqueosProcessService::class)->procesarBloqueosDuplicados();
            }

            $resultados['success'] = true;
        } catch (\Throwable $e) {
            Log::error('Error en la secuencia de importación de bloqueos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $resultados['success'] = false;
            $resultados['error'] = $e->getMessage();
        }

        return $resultados;
    }
}
