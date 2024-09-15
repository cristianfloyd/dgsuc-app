<?php

use App\Helpers\MoneyFormatter;

if (!function_exists('money')) {
    function money($value)
    {
        return MoneyFormatter::format($value);
    }
}
