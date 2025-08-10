<?php

namespace App\Providers;

use App\ImportService;
use App\Services\TableManagementService;
use Illuminate\Support\ServiceProvider;

class ImportServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ImportService::class, function ($app) {
            return new ImportService($app->make(TableManagementService::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
