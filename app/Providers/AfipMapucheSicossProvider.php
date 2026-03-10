<?php

namespace App\Providers;

use App\Contracts\AfipMapucheSicossRepositoryInterface;
use App\Repositories\AfipMapucheSicossRepository;
use Illuminate\Support\ServiceProvider;

class AfipMapucheSicossProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AfipMapucheSicossRepositoryInterface::class, AfipMapucheSicossRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
