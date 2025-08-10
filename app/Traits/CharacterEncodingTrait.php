<?php

namespace App\Traits;

trait CharacterEncodingTrait
{
    protected function cleanAndEncodeString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        return mb_convert_encoding($value, 'ISO-8859-1', 'auto');
    }
}
