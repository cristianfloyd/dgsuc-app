<?php

namespace App\Services\Mapuche;

use App\Models\Mapuche\Dh22;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para determinar qué tabla usar (dh21 o dh21h) según el período fiscal de la liquidación.
 */
class TableSelectorService
{
    /**
     * Constructor del servicio.
     */
    public function __construct(protected PeriodoFiscalService $periodoFiscalService)
    {
    }

    /**
     * Determina qué tabla usar (dh21 o dh21h) según el período fiscal de la liquidación.
     *
     * @param int|array $liquidacion Número de liquidación o array de liquidaciones
     *
     * @return string Nombre de la tabla a usar ('dh21' o 'dh21h')
     */
    public function getDh21TableName($liquidacion): string
    {
        // Si es un array, tomamos la primera liquidación para determinar el período
        $nroLiqui = \is_array($liquidacion) ? $liquidacion[0] : $liquidacion;

        try {
            // Obtener el período fiscal de la liquidación
            $liquidacionModel = Dh22::where('nro_liqui', $nroLiqui)->first();

            if (!$liquidacionModel) {
                Log::warning("Liquidación no encontrada: {$nroLiqui}. Usando tabla dh21 por defecto.");
                return 'dh21';
            }

            // Obtener el período fiscal actual de la base de datos
            $periodoActual = $this->periodoFiscalService->getPeriodoFiscalFromDatabase();

            // Convertir a enteros para comparación
            $yearLiquidacion = (int)$liquidacionModel->per_liano;
            $mesLiquidacion = (int)$liquidacionModel->per_limes;
            $yearActual = (int)$periodoActual['year'];
            $mesActual = (int)$periodoActual['month'];

            // Comparar períodos fiscales
            if (
                $yearLiquidacion < $yearActual ||
                ($yearLiquidacion == $yearActual && $mesLiquidacion < $mesActual)
            ) {
                Log::info("Usando tabla histórica dh21h para liquidación {$nroLiqui} del período {$yearLiquidacion}-{$mesLiquidacion}");
                return 'dh21h';
            }

            Log::info("Usando tabla actual dh21 para liquidación {$nroLiqui} del período {$yearLiquidacion}-{$mesLiquidacion}");
            return 'dh21';
        } catch (\Exception $e) {
            Log::error("Error al determinar la tabla para la liquidación {$nroLiqui}: " . $e->getMessage());
            return 'dh21'; // Por defecto, usamos la tabla actual
        }
    }

    /**
     * Genera la consulta SQL con la tabla correcta según el período fiscal.
     *
     * @param string $query Consulta SQL original con placeholder {TABLE}
     * @param int|array $liquidacion Número de liquidación o array de liquidaciones
     *
     * @return string Consulta SQL con la tabla correcta
     */
    public function replaceTableInQuery(string $query, $liquidacion): string
    {
        $tableName = $this->getDh21TableName($liquidacion);
        return str_replace('{TABLE}', "mapuche.{$tableName}", $query);
    }
}
