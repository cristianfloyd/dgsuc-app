<?php

namespace App;

/**
 * Clase auxiliar para manejar las consultas SQL y sus bindings.
 */
class QueryBuilder
{
    private string $sql;

    private array $bindings;

    public function __construct(string $sql, array $bindings = [])
    {
        $this->sql = $sql;
        $this->bindings = $bindings;
    }

    public function getSql(): string
    {
        return $this->sql;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }
}
