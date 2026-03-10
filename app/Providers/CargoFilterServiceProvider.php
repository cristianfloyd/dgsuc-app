<?php

namespace App\Providers;

use App\Contracts\CargoFilterServiceInterface;
use App\Services\Mapuche\CargoFilterService;
use Illuminate\Support\ServiceProvider;

class CargoFilterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(CargoFilterServiceInterface::class, CargoFilterService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
