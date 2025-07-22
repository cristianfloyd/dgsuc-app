<?php

namespace App\Contracts;

interface RepEmbarazadaServiceInterface
{
    public function ensureTableExists(): void;

    public function populateFromMapuche(): int;

    public function truncateTable(): void;
}
