<?php

namespace App\Services\Mapuche;

use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;

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
        return [
            'year' => session('year'),
            'month' => session('month'),
        ];
    }
}
