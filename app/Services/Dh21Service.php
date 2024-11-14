<?php

namespace App\Services;

use App\Models\Dh21;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;

class Dh21Service
{
    protected $dh21;
    /**
     * Crea una nueva instancia de la clase Dh21Service.
     */
    public function __construct(Dh21 $dh21)
    {
        //
        $this->dh21 = $dh21;
    }

    /**
     * Obtiene la suma total del concepto 101 en la tabla.
     *
     * @return float
     */
    public function totalConcepto101(int $nro_liqui = null)
    {
        $query = $this->dh21->where('codn_conce', '101');

        if ($nro_liqui !== null) {
            if ($nro_liqui <= 0) {
                throw new InvalidArgumentException('El número de liquidación debe ser positivo');
            }
            $query->where('nro_liqui', $nro_liqui);
        }

        return $query->sum('impp_conce');
    }

    /**
     * Obtiene una consulta para calcular los totales de los conceptos de una liquidación.
     *
     * @param int|null $nro_liqui Número de liquidación (opcional)
     * @param int|null $codn_fuent Código de fuente (opcional)
     * @return \Illuminate\Database\Eloquent\Builder Consulta para calcular los totales de los conceptos
     */
    public function conceptosTotales(int $nro_liqui = null, int $codn_fuent = null): Builder
    {
        try {
            // Construcción de la consulta base
            $query = $this->dh21->query()
                ->select(
                    DB::raw('ROW_NUMBER() OVER (ORDER BY codn_conce) as id_liquidacion'),
                    'codn_conce',
                    DB::raw('SUM(impp_conce) as total_impp')
                )
                // Filtro opcional por nro_liqui
                ->when($nro_liqui !== null, function ($query) use ($nro_liqui) {
                    return $query->where('nro_liqui', '=', $nro_liqui);
                })
                // Filtro por codn_conce mayor a 100
                ->where('codn_conce', '>', '100')
                // Filtro opcional por codn_fuent
                ->when($codn_fuent !== null, function ($query) use ($codn_fuent) {
                    return $query->where('codn_fuent', '=', $codn_fuent);
                })
                // Filtro adicional por codn_conce
                ->whereRaw('codn_conce/100 IN (1,3)')
                // Agrupación por codn_conce
                ->groupBy('codn_conce')
                // Ordenación por codn_conce
                ->orderBy('codn_conce');

            return $query;
        } catch (\Exception $e) {
            // Manejo de excepciones
            Log::error('Error en conceptosTotales: ' . $e->getMessage());
            throw $e;
        }
    }
}
