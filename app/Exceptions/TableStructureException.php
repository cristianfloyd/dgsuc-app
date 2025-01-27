<?php

namespace App\Exceptions;

use Exception;

class TableStructureException extends Exception
{
    protected string $tableName;
    protected array $missingColumns;
    protected array $extraColumns;

    public function __construct(
        string $tableName,
        array $missingColumns = [],
        array $extraColumns = [],
        ?string $message = null
    ) {
        $this->tableName = $tableName;
        $this->missingColumns = $missingColumns;
        $this->extraColumns = $extraColumns;

        $message = $message ?? $this->buildMessage();
        parent::__construct($message);
    }

    protected function buildMessage(): string
    {
        $parts = ["Estructura inválida en tabla {$this->tableName}"];

        if (!empty($this->missingColumns)) {
            $parts[] = "Columnas faltantes: " . implode(', ', $this->missingColumns);
        }

        if (!empty($this->extraColumns)) {
            $parts[] = "Columnas extra: " . implode(', ', $this->extraColumns);
        }

        return implode(". ", $parts);
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getMissingColumns(): array
    {
        return $this->missingColumns;
    }

    public function getExtraColumns(): array
    {
        return $this->extraColumns;
    }
}
