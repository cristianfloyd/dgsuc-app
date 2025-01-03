<?php

namespace App\Traits\Mapuche;

use App\Services\EncodingService;
use Illuminate\Support\Facades\Log;

trait EncodingTrait {
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        if (in_array($key, $this->encodedFields ?? [])) {
            Log::debug("Encoding conversion for {$key}", [
                'original' => bin2hex($value),
                'converted' => bin2hex(EncodingService::toUtf8($value))
            ]);
            return EncodingService::toUtf8($value);
        }
        return $value;
    }
}
