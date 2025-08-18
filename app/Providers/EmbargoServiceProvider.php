<?php

namespace App\Providers;

use App\Services\EmbargoTableService;
use Illuminate\Support\ServiceProvider;

class EmbargoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(EmbargoTableService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Solo ejecutar si la aplicación está completamente booteada
        if ($this->app->isBooted()) {
            $tableService = $this->app->make(EmbargoTableService::class);
            $tableService->ensureTableExists();
        }
    }
}
