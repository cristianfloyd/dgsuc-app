<?php

namespace App\Services\Mapuche;

use App\Models\Mapuche\Dh22;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Dh22Service
{


    /**
     * Obtener liquidaciones definitivas con período fiscal formateado y filtros opcionales.     *
     * @param int|null $year
     * @param int|null $month
     * @return Collection
     */
    public function getLiquidacionesDefinitivas(?int $year = null, ?int $month = null): Collection
    {
        try {
            $query = Dh22::query()
                ->select([
                    'nro_liqui',
                    'desc_liqui',
                    DB::raw("CONCAT(per_liano, LPAD(per_limes, 2, '0')) as fiscal_period")
                ])
                ->whereRaw("LOWER(desc_liqui) LIKE '%definitiva%'");

            if ($year) {
                $query->where('per_liano', $year);
            }

            if ($month) {
                $query->where('per_limes', $month);
            }

            return $query->orderBy('per_liano', 'desc')
                         ->orderBy('per_limes', 'desc')
                         ->get();
        } catch (\Exception $e) {
            Log::error('Error getting definitive settlements: ' . $e->getMessage());
            return collect();
        }
    }
}
