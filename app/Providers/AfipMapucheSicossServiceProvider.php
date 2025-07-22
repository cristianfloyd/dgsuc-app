<?php

namespace App\Providers;

use App\Contracts\TableService\AfipMapucheSicossTableServiceInterface;
use App\Services\AfipMapucheSicossTableService;
use Illuminate\Support\ServiceProvider;

class AfipMapucheSicossServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AfipMapucheSicossTableServiceInterface::class, AfipMapucheSicossTableService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

    }
}
