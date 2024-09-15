<?php

namespace App\Helpers;

class MoneyFormatter
{
    public static function format($value)
    {
        return '$' . number_format($value, 2, ',', '.');
    }
}
