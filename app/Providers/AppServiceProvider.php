<?php

namespace App\Providers;

use App\Services\WorkflowService;
use App\Listeners\JobFailedListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Queue\Events\JobFailed;
use App\Listeners\JobProcessedListener;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobProcessed;
use App\Contracts\WorkflowServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WorkflowServiceInterface::class, WorkflowService::class);
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        Event::listen(
            JobFailed::class,
            JobFailedListener::class,
        );

        Event::listen(
            JobProcessed::class, // Asumiendo que el evento está en App\Events
            JobProcessedListener::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
