<?php

namespace App\Services\Mapuche;

use App\Models\Mapuche\Dh22;

class LiquidacionService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {

    }

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
        $resultado = Dh22::query()
            ->where('per_liano', $year)
            ->where('per_limes', $month)
            ->pluck('desc_liqui', 'nro_liqui')
            ->toArray();

        return $resultado;
    }

    /**
     * Obtiene la liquidación definitiva para un período fiscal.
     *
     * @param string $year
     * @param string $month
     *
     * @return Dh22|null
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
