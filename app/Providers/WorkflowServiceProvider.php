<?php

namespace App\Providers;

use App\Services\WorkflowService;
use App\Services\ProcessLogService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class WorkflowServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ProcessLogService::class, function ($app) {
            return new ProcessLogService();
        });

        $this->app->singleton(WorkflowService::class, function ($app) {
            return new WorkflowService($app->make(ProcessLogService::class));
        });

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [ProcessLogService::class, WorkflowService::class];
    }
}
