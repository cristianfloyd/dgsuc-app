<?php

namespace App\Providers;

use App\TableVerificationService;
use Illuminate\Support\ServiceProvider;

class TableVerificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(TableVerificationService::class, function ($app) {
            return new TableVerificationService();
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
