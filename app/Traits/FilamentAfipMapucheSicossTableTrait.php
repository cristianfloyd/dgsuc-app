<?php

namespace App\Traits;

use App\Services\AfipMapucheSicossTableService;

trait FilamentAfipMapucheSicossTableTrait
{
    protected function getTableServiceClass(): string
    {
        return AfipMapucheSicossTableService::class;
    }
}
