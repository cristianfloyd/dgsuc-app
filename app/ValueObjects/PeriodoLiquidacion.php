<?php

namespace App\ValueObjects;

use Carbon\Carbon;
use InvalidArgumentException;

class PeriodoLiquidacion
{
    private readonly Carbon $fecha;

    public function __construct(string $year, string $month)
    {
        $fecha = Carbon::createFromFormat('Y-m', "$year-$month");
        if (!$fecha instanceof Carbon) {
            throw new InvalidArgumentException("Invalid date format: $year-$month");
        }
        $this->fecha = $fecha->startOfMonth();
    }

    /**
     * Devuelve la fecha de referencia del periodo de liquidación.
     *
     * @return Carbon La fecha de referencia del periodo de liquidación.
     */
    public function getFechaReferencia(): Carbon
    {
        return $this->fecha;
    }

    /**
     * Devuelve la fecha de inicio del periodo de liquidación, que es 1 mes antes de la fecha de referencia.
     *
     * @return Carbon La fecha de inicio del periodo de liquidación.
     */
    public function getFechaInicio(): Carbon
    {
        return $this->fecha->copy()->subMonths();
    }

    /**
     * Devuelve la fecha correspondiente al cuarto mes anterior a la fecha de referencia del periodo de liquidación.
     *
     * @return Carbon La fecha correspondiente al cuarto mes anterior a la fecha de referencia.
     */
    public function getFechaCuartoMes(): Carbon
    {
        return $this->fecha->copy()->subMonths(3);
    }

    /**
     * Devuelve la fecha correspondiente al tercer mes anterior a la fecha de referencia del periodo de liquidación.
     *
     * @return Carbon La fecha correspondiente al tercer mes anterior a la fecha de referencia.
     */
    public function getFechaTercerMes(): Carbon
    {
        return $this->fecha->copy()->subMonths(3);
    }

    public function getFechaSegundoMes(): Carbon
    {
        return $this->fecha->copy()->subMonths(2);
    }

    /**
     * Devuelve el año correspondiente a la fecha de referencia del periodo de liquidación.
     *
     * @return string El año correspondiente a la fecha de referencia del periodo de liquidación.
     */
    public function getYear(): string
    {
        return $this->fecha->format('Y');
    }

    /**
     * Devuelve el mes correspondiente a la fecha de referencia del periodo de liquidación.
     *
     * @return string El mes correspondiente a la fecha de referencia del periodo de liquidación.
     */
    public function getMonth(): string
    {
        return $this->fecha->format('m');
    }
}
