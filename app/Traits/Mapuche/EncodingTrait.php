<?php

namespace App\Traits\Mapuche;

use App\Services\EncodingService;

trait EncodingTrait {
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        return in_array($key, $this->encodedFields ?? [])
            ? EncodingService::toUtf8($value)
            : $value;
    }
}
