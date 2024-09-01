<?php

namespace App\Services\Mapuche;

use App\Models\Dh61;
use App\Models\Dh99;
use Illuminate\Support\Facades\Log;

/**
 * Clase PeriodoFiscalService
 *
 * Esta clase proporciona servicios relacionados con los períodos fiscales.
 *
 * @package App\Services\Mapuche
 *
 * @method setPeriodoFiscal(int $year, int $month) Establece el período fiscal actual en la sesión.
 * @method array getPeriodoFiscal() Obtiene el período fiscal actual almacenado en la sesión.
 * @method array getPeriodoFiscalFromDatabase() Obtiene el período fiscal actual almacenado en la base de datos.
 *
 */
class PeriodoFiscalService
{
    /**
     * Establece el período fiscal actual en la sesión.
     *
     * @param int $year El año del período fiscal.
     * @param int $month El mes del período fiscal.
     */
    public function setPeriodoFiscal(int $year, int $month): void
    {
        session(['year' => $year, 'month' => $month]);
        Log::debug("Período fiscal establecido en la sesión: $year-$month");
    }

    /**
     * Obtiene el período fiscal actual almacenado en la sesión.
     *
     * @return array Devuelve un array con las claves 'year' y 'month' que contienen el año y mes del período fiscal actual.
     */
    public function getPeriodoFiscal(): array
    {
        if (session()->has('year') && session()->has('month')) {
            return [
                'year' => session('year'),
                'month' => session('month'),
            ];
        } else {
            // Si no hay un período fiscal establecido en la sesión, devuelve el periodo almacenado en la base de datos dh99
            return $this->getPeriodoFiscalFromDatabase();

        }
    }

    public function getPeriodoFiscalFromDatabase(): array
    {
        $periodoFiscal = Dh99::first();
        return [
            'year' => $periodoFiscal->per_anoct,
            'month' => $periodoFiscal->per_mesct,
        ];
    }

    /**
     * Obtiene los distintos periodos fiscales almacenados en dh61 y los devuelve en un array.
     *
     * @return array Devuelve un array con los periodos fiscales almacenados en dh61.
     */
    public function getPeriodosFiscales(): array
    {
        $periodosFiscales = Dh61::select('per_anoct', 'per_mesct')
            ->distinct()
            ->get();
        return [
            'periodosFiscales' => $periodosFiscales,
        ];
    }
}
