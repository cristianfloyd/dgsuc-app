<?php

namespace App\ValueObjects;

use JsonSerializable;
use InvalidArgumentException;

/**
 * Value Object que representa un número de liquidación en el sistema Mapuche.
 *
 * Este objeto garantiza que el número de liquidación siempre sea un valor válido
 * según las reglas de negocio establecidas.
 */
class NroLiqui implements JsonSerializable
{
    /**
     * @var int El valor del número de liquidación
     */
    private int $value;

    /**
     * Constructor que valida y establece el valor del número de liquidación.
     *
     * @param int $value El número de liquidación
     * @throws InvalidArgumentException Si el valor no cumple con las reglas de validación
     */
    public function __construct(int $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    /**
     * Valida que el número de liquidación cumpla con las reglas de negocio.
     *
     * @param int $value El valor a validar
     * @throws InvalidArgumentException Si el valor no es válido
     */
    private function validate(int $value): void
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('El número de liquidación debe ser positivo');
        }
    }

    /**
     * Crea una nueva instancia a partir de un valor primitivo.
     *
     * @param int $value El número de liquidación
     * @return self
     */
    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    /**
     * Crea una nueva instancia a partir de un string, realizando la conversión adecuada.
     *
     * @param string $value El número de liquidación como string
     * @return self
     * @throws InvalidArgumentException Si el valor no puede convertirse a entero o no es válido
     */
    public static function fromString(string $value): self
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException('El número de liquidación debe ser numérico');
        }

        return new self((int) $value);
    }

    /**
     * Devuelve el valor primitivo del número de liquidación.
     *
     * @return int
     */
    public function value(): int
    {
        return $this->value;
    }

    /**
     * Compara si este número de liquidación es igual a otro.
     *
     * @param NroLiqui $other El otro número de liquidación a comparar
     * @return bool
     */
    public function equals(NroLiqui $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Implementación de JsonSerializable para permitir la serialización directa.
     *
     * @return int
     */
    public function jsonSerialize(): int
    {
        return $this->value;
    }

    /**
     * Representación en string del número de liquidación.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->value;
    }
}
