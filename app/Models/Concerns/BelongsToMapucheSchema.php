<?php

declare(strict_types=1);

namespace App\Models\Concerns;

/** @phpstan-ignore trait.unused (Reserved for future use) */
trait BelongsToMapucheSchema
{
    /**
     * Get the table schema for the model.
     */
    public function getSchemaName(): string
    {
        return 'mapuche';
    }

    /**
     * Get the fully qualified table name including schema.
     */
    public function getQualifiedTableName(): string
    {
        return $this->getSchemaName() . '.' . $this->getTable();
    }
}
