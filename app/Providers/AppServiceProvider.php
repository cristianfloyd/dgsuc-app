<?php

namespace App\Providers;

use Livewire\Livewire;
use App\Services\WorkflowService;
use App\Listeners\JobFailedListener;
use App\Repositories\Dhr2Repository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;
use App\Listeners\JobProcessedListener;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobProcessed;
use App\Contracts\WorkflowServiceInterface;
use App\Filament\Components\PanelSwitcherModal;
use App\Jobs\Middleware\InspectJobDependencies;
use App\Contracts\Repositories\Dhr2RepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WorkflowServiceInterface::class, WorkflowService::class);
        if ($this->app->environment('local')) {
        }

        Event::listen(
            JobFailed::class,
            JobFailedListener::class,
        );

        Event::listen(
            JobProcessed::class, // Asumiendo que el evento está en App\Events
            JobProcessedListener::class
        );

        $this->app->bind(Dhr2RepositoryInterface::class, Dhr2Repository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Queue::before(function ($job) {
            return (new InspectJobDependencies)->handle($job, function ($job) {
                return $job;
            });
        });
        // Registramos el componente Filament
        // $this->loadViewComponentsAs('filament', [
        //     PanelSwitcherModal::class,
        // ]);
        // Livewire::component('panel-switcher-modal', PanelSwitcherModal::class);
    }
}
