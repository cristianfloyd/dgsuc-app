<?php

namespace App\Services\Mapuche;

use App\Models\Mapuche\Dh22;

class LiquidacionService
{
    /**
     * Obtiene las liquidaciones disponibles para un período fiscal específico,
     * formateadas para ser usadas en un componente Select de Filament.
     *
     * @param string $year Año del período fiscal
     * @param string $month Mes del período fiscal
     *
     * @return array Array asociativo con nro_liqui => desc_liqui
     */
    public function getLiquidacionesForSelect(string $year, string $month): array
    {
        return Dh22::query()
            ->where('per_liano', $year)
            ->where('per_limes', $month)
            ->pluck('desc_liqui', 'nro_liqui')
            ->toArray();
    }

    /**
     * Obtiene la liquidación definitiva para un período fiscal.
     */
    public function getLiquidacionDefinitiva(string $year, string $month): ?Dh22
    {
        return Dh22::query()
            ->where('per_liano', $year)
            ->where('per_limes', '0' . $month) // Asegurarse que el mes tenga el formato correcto
            ->definitiva()
            ->orderByDesc('nro_liqui')
            ->first();
    }
}
