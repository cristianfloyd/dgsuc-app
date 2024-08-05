<?php

namespace App\Providers;

use App\Repositories\CuilRepository;
use Illuminate\Support\ServiceProvider;
use App\Contracts\CuilRepositoryInterface;

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
        //
    }
}
