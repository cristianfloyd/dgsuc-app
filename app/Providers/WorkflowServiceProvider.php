<?php

namespace App\Providers;

use App\Contracts\WorkflowExecutionInterface;
use App\Services\WorkflowService;
use App\Services\ProcessLogService;
use Illuminate\Support\ServiceProvider;
use App\Contracts\WorkflowServiceInterface;
use app\Services\ProcessInitializationService;
use App\Services\WorkflowExecutionService;
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


        $this->app->singleton(ProcessInitializationService::class, function ($app) {
            return new ProcessInitializationService(
            $app->make(WorkflowService::class),
            $app->make(ProcessLogService::class));
        });

        $this->app->bind(WorkflowExecutionInterface::class, WorkflowExecutionService::class);
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
