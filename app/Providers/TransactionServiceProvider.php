<?php

namespace App\Providers;

use App\Services\TransactionService;
use Illuminate\Support\ServiceProvider;
use App\Contracts\TransactionServiceInterface;

class TransactionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(TransactionServiceInterface::class, TransactionService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
