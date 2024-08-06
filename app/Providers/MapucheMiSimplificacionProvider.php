<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MapucheMiSimplificacionService;
use App\Contracts\MapucheMiSimplificacionServiceInterface;

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
        //
    }
}
