<?php

namespace App\Providers;

use App\Contracts\WorkflowExecutionInterface;
use app\Services\ProcessInitializationService;
use App\Services\ProcessLogService;
use App\Services\WorkflowExecutionService;
use App\Services\WorkflowService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

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


        $this->app->singleton(ProcessInitializationService::class, function ($app) {
            return new ProcessInitializationService(
                $app->make(WorkflowService::class),
                $app->make(ProcessLogService::class),
            );
        });

        $this->app->bind(WorkflowExecutionInterface::class, WorkflowExecutionService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

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
