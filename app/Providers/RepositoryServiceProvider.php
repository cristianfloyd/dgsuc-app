<?php

namespace App\Providers;

use App\Services\CategoryUpdateService;
use App\Repositories\Dh11Repository;
use App\Repositories\Dh61Repository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Dh11RepositoryInterface;
use App\Repositories\Dh61RepositoryInterface;
use App\Contracts\CategoryUpdateServiceInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(Dh11RepositoryInterface::class, Dh11Repository::class);
        $this->app->bind(Dh61RepositoryInterface::class, Dh61Repository::class);
        $this->app->bind(CategoryUpdateServiceInterface::class, CategoryUpdateService::class);

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
