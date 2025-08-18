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
        $tableService = $this->app->make(EmbargoTableService::class);
        $tableService->ensureTableExists();
    }
}
