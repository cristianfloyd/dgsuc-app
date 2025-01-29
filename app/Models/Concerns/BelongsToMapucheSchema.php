<?php

declare(strict_types=1);

namespace App\Models\Concerns;

trait BelongsToMapucheSchema
{
    /**
     * Get the table schema for the model.
     *
     * @return string
     */
    public function getSchemaName(): string
    {
        return 'mapuche';
    }

    /**
     * Get the fully qualified table name including schema.
     *
     * @return string
     */
    public function getQualifiedTableName(): string
    {
        return $this->getSchemaName() . '.' . $this->getTable();
    }
}
