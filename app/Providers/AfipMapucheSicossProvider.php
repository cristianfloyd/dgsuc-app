<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\AfipMapucheSicossRepository;
use App\Repositories\Interfaces\AfipMapucheSicossRepositoryInterface;

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
        //
    }
}
