<?php

namespace App\Services;

use App\Exceptions\ValidationException;
use Carbon\Carbon;

class DateParserService
{
    /**
     * Formatos de fecha soportados.
     */
    private array $supportedFormats = [
        'd/m/Y',
        'Y-m-d',
        'd-m-Y',
        'd/m/y',
        'Y/m/d',
    ];

    /**
     * Parsea una fecha en múltiples formatos.
     */
    public function parseDate($date): Carbon
    {
        if ($date instanceof Carbon) {
            return $date;
        }

        if ($date instanceof \DateTime) {
            return Carbon::instance($date);
        }

        foreach ($this->supportedFormats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $date);
                if ($parsed && $this->isValidYear($parsed->year)) {
                    return $parsed;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        throw new ValidationException("Formato de fecha no válido: {$date}");
    }

    /**
     * Valida que el año esté en un rango razonable.
     */
    private function isValidYear(int $year): bool
    {
        return $year >= 1900 && $year <= 2100;
    }
}
