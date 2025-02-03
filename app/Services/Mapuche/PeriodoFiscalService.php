<?php

namespace App\Services\Mapuche;

use App\Models\Dh61;
use App\Models\Dh99;
use App\Models\Mapuche\Dh22;
use Illuminate\Support\Facades\Log;

/**
 * Clase PeriodoFiscalService
 *
 * Esta clase proporciona servicios relacionados con los períodos fiscales.
 *
 * @package App\Services\Mapuche
 *
 * @method void setPeriodoFiscal(int $year, int $month) Establece el período fiscal actual en la sesión.
 * @method array getPeriodoFiscal Obtiene el período fiscal actual almacenado en la sesión.
 * @method array getPeriodoFiscalFromDatabase Obtiene el período fiscal actual almacenado en la base de datos.
 *
 */
class PeriodoFiscalService
{
    /**
    * Determina si una liquidación corresponde al período actual
     */
    public function isPeriodoActual(int $nroLiqui): bool
    {
        $liquidacion = Dh22::query()->where('nro_liqui', $nroLiqui)->first();
        $periodoActual = $this->getPeriodoFiscalFromDatabase();

        if (!$liquidacion) {
            return false;
        }

        return $liquidacion->per_liano === (int)$periodoActual['year']
            && $liquidacion->per_limes === (int)$periodoActual['month'];
    }

    /**
     * Establece el período fiscal actual en la sesión.
     *
     * @param int $year El año del período fiscal.
     * @param int $month El mes del período fiscal.
     */
    public function setPeriodoFiscal(int $year, int $month): void
    {
        $formattedYear = strval($year);
        $formattedMonth = sprintf('%02d', $month);
        session(['year' => $formattedYear, 'month' => $formattedMonth]);
        Log::debug("Período fiscal establecido en la sesión: $formattedYear-$formattedMonth");
    }

    /**
     * Obtiene el período fiscal actual almacenado en la sesión.
     *
     * @return array Devuelve un array con las claves 'year' y 'month' que contienen el año y mes del período fiscal actual.
     */
    public function getPeriodoFiscal(): array
    {
        if (session()->has(['year', 'month'])) {
            log::debug("Período fiscal obtenido de la sesión: " . session('year') . "-" . session('month'));
            return [
                'year' => session('year'),
                'month' => session('month'),
            ];
        } else {
            // Si no hay un período fiscal establecido en la sesión, devuelve el periodo almacenado en la base de datos dh99
            return $this->getPeriodoFiscalFromDatabase();

        }
    }

    /**
     * Obtiene el periodo fiscal de la base de datos.
     *
     * @return array Un array con el año y el mes del periodo fiscal en el formato ['year' => 'YYYY', 'month' => 'MM'].
     */
    public function getPeriodoFiscalFromDatabase(): array
    {
        // Obtiene el primer registro de la tabla Dh99. Asumimos que siempre existe un periodo fiscal definido.
        $periodoFiscal = Dh99::first();

        // Formatea el año y el mes al formato deseado.
        $formattedYear = strval($periodoFiscal->per_anoct);
        $formattedMonth = sprintf('%02d', $periodoFiscal->per_mesct);

        return [
            'year' => $formattedYear,
            'month' => $formattedMonth,
        ];
    }

    /**
     * Obtiene los distintos periodos fiscales almacenados en Dh22 y los devuelve en un array.
     *
     * @return array Devuelve un array con los periodos fiscales almacenados en dh61.
     */
    public function getPeriodosFiscales(): array
    {
        $periodosFiscales = Dh22::query()->select('per_liano', 'per_limes')
            ->distinct()
            ->get();
        return [
            'periodosFiscales' => $periodosFiscales,
        ];
    }

    public function getYear()
    {
        $periodoFiscal = $this->getPeriodoFiscal();
        return $periodoFiscal['year'];
    }

    public function getMonth()
    {
        $periodoFiscal = $this->getPeriodoFiscal();
        return $periodoFiscal['month'];
    }

    public function getPeriodoFiscalFromId(int $id = null): array
    {
        if ($id === null) {
            Log::warning('No se proporcionó un ID para obtener el período fiscal');
            return $this->getPeriodoFiscalFromDatabase();
        }
        $periodoFiscal = Dh22::find($id);
        return [
            'year' => $periodoFiscal->per_liano,
            'month' => $periodoFiscal->per_limes
            ];
    }
}
