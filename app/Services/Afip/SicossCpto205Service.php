<?php

declare(strict_types=1);

namespace App\Services\Afip;

use Illuminate\Support\Facades\Log;
use App\Repositories\Afip\SicossCpto205Repository;

class SicossCpto205Service
{
    protected SicossCpto205Repository $repository;
    
    /**
     * Constructor
     * 
     * @param SicossCpto205Repository $repository Repositorio para operaciones de datos
     */
    public function __construct(SicossCpto205Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Actualiza datos relacionados con el concepto 205 en SICOSS
     *
     * Este método crea una tabla temporal con información de agentes que
     * tienen el concepto 789, calculando el monto como un porcentaje (50%)
     * del total de dicho concepto, pero solo para aquellos que también tienen
     * el concepto 205 y están en la tabla de control_aportes_diferencias.
     *
     * @param array $params Parámetros adicionales (opcional)
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
                'data' => null
            ];
        }

        try {
            // Iniciar transacción
            $this->repository->iniciarTransaccion();
            
            // Crear tabla temporal y obtener número de registros
            $totalRegistros = $this->repository->crearTablaTemporal($liquidaciones);
            
            // Puedes agregar aquí cualquier actualización adicional usando esta tabla temporal
            // Por ejemplo, actualizar alguna tabla de SICOSS con estos datos

            // $this->repository->confirmarTransaccion();
            $this->repository->revertirTransaccion();  // Descomentar para probar

            return [
                'status' => 'success',
                'message' => "Se procesaron $totalRegistros registros para el concepto 205",
                'data' => [
                    'registros_procesados' => $totalRegistros,
                    'liquidaciones' => $liquidaciones
                ]
            ];

        } catch (\Exception $e) {
            $this->repository->revertirTransaccion();

            Log::error('Error en actualización de concepto 205', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
}
