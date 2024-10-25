<?php

namespace App\Providers;

use App\Services\EmployeeService;
use Illuminate\Support\ServiceProvider;
use App\Repositories\EmployeeRepository;
use App\Contracts\EmployeeServiceInterface;
use App\Contracts\EmployeeRepositoryInterface;

class EmployeeRepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(EmployeeServiceInterface::class, EmployeeService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
