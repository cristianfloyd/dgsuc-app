<?php

namespace App\Traits\Mapuche;

use App\Services\EncodingService;

use function in_array;

trait EncodingTrait
{
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        if (in_array($key, $this->encodedFields ?? [])) {
            return EncodingService::toUtf8($value);
        }
        return $value;
    }
}
