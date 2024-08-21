<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Mapuche\CargoFilterService;
use App\Contracts\CargoFilterServiceInterface;

class CargoFilterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind( CargoFilterServiceInterface::class, CargoFilterService::class );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
