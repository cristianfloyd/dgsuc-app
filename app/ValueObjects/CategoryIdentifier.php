<?php

declare(strict_types=1);

namespace App\ValueObjects;

readonly class CategoryIdentifier implements \Stringable
{
    private const int CATEGORY_LENGTH = 4;

    public function __construct(
        private string $category,
        private int $year,
        private int $month,
    ) {
        $this->validate();
    }

    public function __toString(): string
    {
        return \sprintf(
            '%s-%d-%d',
            $this->getNormalizedCategory(),
            $this->year,
            $this->month,
        );
    }

    public function getCategory(): string
    {
        // Mantiene el formato bpchar(4) rellenando con espacios
        return str_pad(trim($this->category), self::CATEGORY_LENGTH);
    }

    public function getNormalizedCategory(): string
    {
        return trim($this->category);
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public static function fromString(string $value): self
    {
        $parts = explode('-', $value);

        if (\count($parts) !== 3) {
            throw new \InvalidArgumentException(
                'El formato del identificador debe ser: categoria-año-mes',
            );
        }

        return new self(
            category: $parts[0],
            year: (int) $parts[1],
            month: (int) $parts[2],
        );
    }

    private function validate(): void
    {
        $trimmedCategory = trim($this->category);

        if ($trimmedCategory === '' || $trimmedCategory === '0') {
            throw new \InvalidArgumentException('La categoría es requerida');
        }

        if (\strlen($trimmedCategory) > self::CATEGORY_LENGTH) {
            throw new \InvalidArgumentException(
                'La categoría no puede exceder {self::CATEGORY_LENGTH} caracteres',
            );
        }

        if ($this->year < 1900 || $this->year > 2100) {
            throw new \InvalidArgumentException('El año debe estar entre 1900 y 2100');
        }

        if ($this->month < 1 || $this->month > 12) {
            throw new \InvalidArgumentException('El mes debe estar entre 1 y 12');
        }
    }
}
