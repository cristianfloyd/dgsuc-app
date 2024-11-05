<?php

namespace App\Traits;

trait CharacterEncodingTrait
{
    protected function cleanAndEncodeString(?string $value): ?string
    {
        if (is_null($value)) return null;

        $utf8Value = mb_convert_encoding($value, 'ISO-8859-1', 'auto');
        return $utf8Value;
    }
}
