<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface TableManagementServiceInterface
{
    public static function verifyAndPrepareTable(string $tableName, string $connection = null): void;
    public static function verifyTableIsEmpty(Model $model, string $tableName): bool;
}
