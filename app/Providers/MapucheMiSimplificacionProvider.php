<?php

namespace App\Providers;

use App\Contracts\MapucheMiSimplificacionServiceInterface;
use App\Services\MapucheMiSimplificacionService;
use Illuminate\Support\ServiceProvider;

class MapucheMiSimplificacionProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(MapucheMiSimplificacionServiceInterface::class, MapucheMiSimplificacionService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

    }
}
