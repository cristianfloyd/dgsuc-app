<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TableVerificationService;

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
