<?php

namespace App\Providers;

use App\Contracts\CuilRepositoryInterface;
use App\Repositories\CuilRepository;
use Illuminate\Support\ServiceProvider;

class CuilsRepositoryProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CuilRepositoryInterface::class, CuilRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
