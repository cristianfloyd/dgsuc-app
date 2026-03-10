<?php

namespace App\Providers;

use App\Contracts\DataMapperInterface;
use App\Services\DataMapperService;
use Illuminate\Support\ServiceProvider;

class DataMapperProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DataMapperInterface::class, DataMapperService::class);
    }

    public function boot(): void
    {
    }
}
