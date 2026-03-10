<?php

namespace App\Services;

use App\Contracts\TableManagementServiceInterface;
use Illuminate\Support\ServiceProvider;

class TableManagementServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->bind(fn($app): \App\Contracts\TableManagementServiceInterface => new TableManagementService());

        $this->app->bind(fn($app): \App\Services\TableManagementService => new TableManagementService());
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
