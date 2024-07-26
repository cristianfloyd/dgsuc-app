<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TableVerificationService
{
    public static function verifyTableIsEmpty(Model $model,string $tableName): bool
    {
        $tableIsEmpty = $model->all()->isEmpty();

        if ($tableIsEmpty) {
            return false;
        } else {
            return true;
        }
    }
}
