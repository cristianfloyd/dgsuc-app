<?php

declare(strict_types=1);

namespace App\Services\Afip;

use App\Repositories\Afip\SicossCpto205Repository;
use Illuminate\Support\Facades\Log;

class SicossCpto205Service
{
    protected SicossCpto205Repository $repository;

    /**
     * Constructor.
     *
     * @param SicossCpto205Repository $repository Repositorio para operaciones de datos
     */
    public function __construct(SicossCpto205Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Actualiza datos relacionados con el concepto 205 en SICOSS.
     *
     * Este método crea una tabla temporal con información de agentes que
     * tienen el concepto 789, calculando el monto como un porcentaje (50%)
     * del total de dicho concepto, pero solo para aquellos que también tienen
     * el concepto 205 y están en la tabla de control_aportes_diferencias.
     *
     * @param array $params Parámetros adicionales (opcional)
     *
     * @return array Resultado de la operación
     */
    public function actualizarCpto205(array $params = []): array
    {
        // Liquidaciones por defecto: definidas en la consulta original
        $liquidaciones = $params['liquidaciones'] ?? [];
        if (empty($liquidaciones)) {
            return [
                'status' => 'error',
                'message' => 'No se encontraron liquidaciones para actualizar el concepto 205',
                'data' => null,
            ];
        }

        try {
            // Crear tabla temporal y obtener número de registros
            $totalRegistros = $this->repository->procesarConceptos($liquidaciones);

            return [
                'status' => 'success',
                'message' => "Se procesaron $totalRegistros registros para el concepto 205",
                'data' => [
                    'registros_procesados' => $totalRegistros,
                    'liquidaciones' => $liquidaciones,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error en actualización de concepto 205', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    public function actualizarCpto204(array $params = []): array
    {
        $liquidaciones = $params['liquidaciones'] ?? [];
        if (empty($liquidaciones)) {
            return [
                'status' => 'error',
                'message' => 'No se encontraron liquidaciones para actualizar el concepto 204',
                'data' => null,
            ];
        }

        try {
            $totalRegistros = $this->repository->procesarConcepto204($liquidaciones);

            return [
                'status' => 'success',
                'message' => "Se procesaron $totalRegistros registros para el concepto 204",
                'data' => [
                    'registros_procesados' => $totalRegistros,
                    'liquidaciones' => $liquidaciones,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error en actualización de concepto 204', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }
}
