<?php

namespace App\ValueObjects;

use Carbon\Carbon;
use JsonSerializable;
use InvalidArgumentException;

/**
 * Value Object que representa un período fiscal en el sistema.
 *
 * Este objeto encapsula la lógica relacionada con los períodos fiscales,
 * permitiendo su representación tanto en formato combinado (YYYYMM) como
 * en componentes separados (año y mes).
 */
class PeriodoFiscal implements JsonSerializable
{
    /**
     * @var int El año del período fiscal
     */
    private int $year;

    /**
     * @var int El mes del período fiscal (1-12)
     */
    private int $month;

    /**
     * Constructor que valida y establece el año y mes del período fiscal.
     *
     * @param int $year El año del período fiscal
     * @param int $month El mes del período fiscal (1-12)
     * @throws InvalidArgumentException Si los valores no cumplen con las reglas de validación
     */
    public function __construct(int $year, int $month)
    {
        $this->validate($year, $month);
        $this->year = $year;
        $this->month = $month;
    }

    /**
     * Valida que el año y mes cumplan con las reglas de negocio.
     *
     * @param int $year El año a validar
     * @param int $month El mes a validar
     * @throws InvalidArgumentException Si los valores no son válidos
     */
    private function validate(int $year, int $month): void
    {
        if ($year < 1900 || $year > 2100) {
            throw new InvalidArgumentException('El año debe estar entre 1900 y 2100');
        }

        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException('El mes debe estar entre 1 y 12');
        }
    }

    /**
     * Crea una instancia a partir de un string en formato YYYYMM.
     *
     * @param string $periodoFiscal El período fiscal en formato YYYYMM
     * @return self
     * @throws InvalidArgumentException Si el formato no es válido
     */
    public static function fromString(string $periodoFiscal): self
    {
        if (!preg_match('/^(\d{4})(\d{2})$/', $periodoFiscal, $matches)) {
            throw new InvalidArgumentException('El período fiscal debe tener el formato YYYYMM');
        }

        $year = (int) $matches[1];
        $month = (int) $matches[2];

        return new self($year, $month);
    }

    /**
     * Crea una instancia a partir de un objeto Carbon.
     *
     * @param Carbon $date La fecha de la que se extraerá el período fiscal
     * @return self
     */
    public static function fromCarbon(Carbon $date): self
    {
        return new self($date->year, $date->month);
    }

    /**
     * Crea una instancia para el período fiscal actual.
     *
     * @return self
     */
    public static function current(): self
    {
        $now = Carbon::now();
        return new self($now->year, $now->month);
    }

    /**
     * Devuelve el año del período fiscal.
     *
     * @return int
     */
    public function year(): int
    {
        return $this->year;
    }

    /**
     * Devuelve el mes del período fiscal.
     *
     * @return int
     */
    public function month(): int
    {
        return $this->month;
    }

    /**
     * Devuelve el mes del período fiscal con formato de dos dígitos.
     *
     * @return string
     */
    public function formattedMonth(): string
    {
        return str_pad($this->month, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Devuelve el período fiscal en formato YYYYMM.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->year . $this->formattedMonth();
    }

    /**
     * Devuelve el período fiscal en formato legible (MM/YYYY).
     *
     * @return string
     */
    public function toReadableString(): string
    {
        return $this->formattedMonth() . '/' . $this->year;
    }

    /**
     * Convierte el período fiscal a un objeto Carbon (primer día del mes).
     *
     * @return Carbon
     */
    public function toCarbon(): Carbon
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->startOfDay();
    }

    /**
     * Devuelve el período fiscal anterior.
     *
     * @return self
     */
    public function previous(): self
    {
        $date = $this->toCarbon()->subMonth();
        return new self($date->year, $date->month);
    }

    /**
     * Devuelve el período fiscal siguiente.
     *
     * @return self
     */
    public function next(): self
    {
        $date = $this->toCarbon()->addMonth();
        return new self($date->year, $date->month);
    }

    /**
     * Compara si este período fiscal es igual a otro.
     *
     * @param PeriodoFiscal $other El otro período fiscal a comparar
     * @return bool
     */
    public function equals(PeriodoFiscal $other): bool
    {
        return $this->year === $other->year && $this->month === $other->month;
    }

    /**
     * Compara si este período fiscal es anterior a otro.
     *
     * @param PeriodoFiscal $other El otro período fiscal a comparar
     * @return bool
     */
    public function isBefore(PeriodoFiscal $other): bool
    {
        if ($this->year < $other->year) {
            return true;
        }

        return $this->year === $other->year && $this->month < $other->month;
    }

    /**
     * Compara si este período fiscal es posterior a otro.
     *
     * @param PeriodoFiscal $other El otro período fiscal a comparar
     * @return bool
     */
    public function isAfter(PeriodoFiscal $other): bool
    {
        if ($this->year > $other->year) {
            return true;
        }

        return $this->year === $other->year && $this->month > $other->month;
    }

    /**
     * Implementación de JsonSerializable para permitir la serialización directa.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'year' => $this->year,
            'month' => $this->month,
            'formatted' => $this->toString()
        ];
    }

    /**
     * Representación en string del período fiscal.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Devuelve un array con el año y mes para usar en modelos.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'year' => $this->year,
            'month' => $this->month
        ];
    }
}
