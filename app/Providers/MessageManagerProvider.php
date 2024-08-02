<?php

namespace App\Providers;

use App\Services\MessageManager;
use Illuminate\Support\ServiceProvider;
use App\Contracts\MessageManagerInterface;

class MessageManagerProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(MessageManagerInterface::class, MessageManager::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
