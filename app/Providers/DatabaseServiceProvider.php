<?php

namespace App\Providers;

use App\Contracts\DatabaseServiceInterface;
use App\services\DatabaseService;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(DatabaseServiceInterface::class, DatabaseService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
