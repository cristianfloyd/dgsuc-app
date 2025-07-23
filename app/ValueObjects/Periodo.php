<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Representa un periodo de tiempo en formato YYYYMM.
 *
 * Esta clase encapsula la validación y el acceso al valor del periodo.
 */
class Periodo implements Stringable
{
    private readonly string $value;

    public function __construct(string $periodo)
    {
        if (!preg_match('/^\d{6}$/', $periodo)) {
            throw new InvalidArgumentException('El periodo debe ser una cadena de 6 dígitos.');
        }
        $this->value = $periodo;
    }


    /**
     * Devuelve la representación en string del periodo.
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Obtiene el valor del periodo.
     *
     * @return string El valor del periodo en formato YYYYMM
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
