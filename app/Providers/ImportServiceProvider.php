<?php

namespace App\Providers;

use App\ImportService;
use Illuminate\Support\ServiceProvider;
use App\Services\TableManagementService;

class ImportServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ImportService::class, function ($app) {
            return new ImportService($app->make(TableManagementService::class));
            ;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
