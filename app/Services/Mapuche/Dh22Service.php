<?php

namespace App\Services\Mapuche;

use App\Models\Mapuche\Dh22;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Dh22Service
{
    /**
     * Obtener liquidaciones definitivas con período fiscal formateado y filtros opcionales.     *.
     */
    public function getLiquidacionesDefinitivas(?int $year = null, ?int $month = null): Collection
    {
        try {
            $query = Dh22::query()
                ->select([
                    'nro_liqui',
                    'desc_liqui',
                    DB::raw("CONCAT(per_liano, LPAD(per_limes, 2, '0')) as periodo_fiscal"),
                ])
                ->whereRaw("LOWER(desc_liqui) LIKE '%definitiva%'");

            if ($year) {
                $query->where('per_liano', $year);
            }

            if ($month) {
                $query->where('per_limes', $month);
            }

            $query->orderBy('per_liano', 'desc')
                ->orderBy('per_limes', 'desc');

            return $query->get();
        } catch (Exception $e) {
            Log::error('Error getting definitive settlements: ' . $e->getMessage());

            return collect();
        }
    }

    public static function getLiquidacionesParaSelect(): array
    {
        return Dh22::query()
            ->definitiva()
            ->get()
            ->mapWithKeys(fn($liquidacion): array => [
                $liquidacion->nro_liqui => $liquidacion->desc_liqui,
            ])
            ->toArray();
    }
}
