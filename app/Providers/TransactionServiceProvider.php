<?php

namespace App\Providers;

use App\Contracts\TransactionServiceInterface;
use App\Services\TransactionService;
use Illuminate\Support\ServiceProvider;

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

    }
}
