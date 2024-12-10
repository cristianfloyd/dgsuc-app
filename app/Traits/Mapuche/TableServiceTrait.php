<?php

namespace App\Traits\Mapuche;

use Illuminate\Support\Facades\DB;

trait TableServiceTrait
{
    abstract protected function getTableName(): string;

    protected function addLaravelPrimaryKey($table): void
    {
        $table->bigIncrements('id');
        $table->timestamps();
    }

    protected function getNextId(): int
    {
        return DB::connection($this->getConnectionName())
            ->table($this->getTableName())
            ->max('id') + 1;
    }
}
