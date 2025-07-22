<?php

namespace App\Enums;

readonly class LegajoCargo
{
    public function __construct(
        public ?int $legajo = null,
        public ?int $cargo = null,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function getLegajo(): int
    {
        return $this->legajo;
    }

    public function getCargo(): int
    {
        return $this->cargo;
    }

    public function toString(): string
    {
        return "{$this->legajo}-{$this->cargo}";
    }

    public static function from(?int $legajo = null, ?int $cargo = null): self
    {
        return new self($legajo, $cargo);
    }

    public static function fromString(?string $value): self
    {
        if (!$value) {
            return new self();
        }

        [$legajo, $cargo] = explode('-', $value);
        return new self((int)$legajo, (int)$cargo);
    }

    public function isValid(): bool
    {
        return $this->legajo !== null && $this->cargo !== null;
    }
}
