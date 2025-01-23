<?php

namespace App\Providers;


use App\Repositories\Dh11Repository;
use App\Repositories\Dh19Repository;
use App\Repositories\Dh61Repository;
use App\Repositories\EmbargoRepository;
use App\Services\CategoryUpdateService;
use Illuminate\Support\ServiceProvider;
use App\Contracts\Dh19RepositoryInterface;
use App\Repositories\RepOrdenPagoRepository;
use App\Repositories\Dh11RepositoryInterface;
use App\Repositories\Dh61RepositoryInterface;
use App\Repositories\Mapuche\Dh21hRepository;
use App\Contracts\CategoryUpdateServiceInterface;
use App\Contracts\RepOrdenPagoRepositoryInterface;
use App\Contracts\Mapuche\Dh21hRepositoryInterface;
use App\Contracts\Repositories\EmbargoRepositoryInterface;

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
        $this->app->bind(RepOrdenPagoRepositoryInterface::class, RepOrdenPagoRepository::class);
        $this->app->bind(Dh19RepositoryInterface::class, Dh19Repository::class);
        $this->app->bind(Dh21hRepositoryInterface::class, Dh21hRepository::class);
        $this->app->bind(EmbargoRepositoryInterface::class, EmbargoRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
