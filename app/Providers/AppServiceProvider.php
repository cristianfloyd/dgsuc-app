<?php

namespace App\Providers;

use App\Services\WorkflowService;
use App\Listeners\JobFailedListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;
use App\Listeners\JobProcessedListener;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobProcessed;
use App\Services\Reportes\BloqueosService;
use App\Contracts\WorkflowServiceInterface;
use App\Services\OrdenesDescuentoTableService;
use App\Jobs\Middleware\InspectJobDependencies;
use App\Services\Reportes\Interfaces\BloqueosServiceInterface;

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

        $this->app->bind(BloqueosServiceInterface::class, BloqueosService::class);
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
