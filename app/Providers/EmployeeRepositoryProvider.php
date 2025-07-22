<?php

namespace App\Providers;

use App\Contracts\EmployeeRepositoryInterface;
use App\Contracts\EmployeeServiceInterface;
use App\Repositories\EmployeeRepository;
use App\Services\EmployeeService;
use Illuminate\Support\ServiceProvider;

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
    }
}
