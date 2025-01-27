<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AfipMapucheSicossTableService;
use App\Contracts\TableService\AfipMapucheSicossTableServiceInterface;

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
        //
    }
}
