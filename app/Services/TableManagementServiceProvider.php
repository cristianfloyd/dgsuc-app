<?php

namespace App\Services;

use Illuminate\Support\ServiceProvider;

class TableManagementServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(TableManagementService::class, function ($app) {
            return new TableManagementService();
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
