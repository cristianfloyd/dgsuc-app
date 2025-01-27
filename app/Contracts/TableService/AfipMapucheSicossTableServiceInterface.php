<?php

namespace App\Contracts\TableService;

interface AfipMapucheSicossTableServiceInterface
{
    public function exists(): bool;
    public function createAndPopulate(): void;
    public function getTableName(): string;
}
