<?php

namespace App\Exceptions;

use Exception;

class TableStructureException extends Exception
{
    public function __construct(
        protected string $tableName,
        protected array $missingColumns = [],
        protected array $extraColumns = [],
        ?string $message = null,
    ) {
        $message ??= $this->buildMessage();
        parent::__construct($message);
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

    protected function buildMessage(): string
    {
        $parts = ["Estructura inválida en tabla {$this->tableName}"];

        if ($this->missingColumns !== []) {
            $parts[] = 'Columnas faltantes: ' . implode(', ', $this->missingColumns);
        }

        if ($this->extraColumns !== []) {
            $parts[] = 'Columnas extra: ' . implode(', ', $this->extraColumns);
        }

        return implode('. ', $parts);
    }
}
