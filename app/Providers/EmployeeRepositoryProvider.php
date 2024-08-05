<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\EmployeeRepository;
use App\Contracts\EmployeeRepositoryInterface;

class EmployeeRepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
