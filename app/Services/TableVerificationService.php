<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class TableVerificationService
{
    public static function verifyTableIsEmpty(Model $model, string $tableName): bool
    {
        $tableIsEmpty = $model->all()->isEmpty();

        return !($tableIsEmpty);

    }
}
