<?php

namespace App\Providers;

use App\Services\WorkflowService;
use App\Listeners\JobFailedListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use App\Services\RepEmbarazadaService;
use Illuminate\Queue\Events\JobFailed;
use App\Listeners\JobProcessedListener;
use Illuminate\Support\ServiceProvider;
use App\Contracts\ExportServiceInterface;
use Illuminate\Queue\Events\JobProcessed;
use App\Services\AfipMapucheExportService;
use App\Services\Reportes\BloqueosService;
use App\Contracts\WorkflowServiceInterface;
use App\Repositories\Mapuche\Dh16Repository;
use App\Services\Sicoss\SicossExportService;
use App\Services\AfipRelacionesActivasService;
use App\Services\OrdenesDescuentoTableService;
use App\Jobs\Middleware\InspectJobDependencies;
use App\Services\Imports\BloqueosImportService;
use App\Services\Sicoss\SicossTxtExportService;
use App\Contracts\RepEmbarazadaServiceInterface;
use App\Services\Reportes\BloqueosProcessService;
use App\Services\Sicoss\SicossExcelExportService;
use App\Services\Sicoss\SicossReportExportService;
use App\Repositories\Mapuche\Dh16RepositoryInterface;
use App\Contracts\Tables\OrdenesDescuentoTableDefinition;
use App\Services\Reportes\Interfaces\BloqueosServiceInterface;
use App\Services\Contracts\AfipRelacionesActivasServiceInterface;

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

        $this->app->bind(BloqueosServiceInterface::class, function ($app, $parameters) {
            return new BloqueosService(
                $app->make(BloqueosImportService::class),
                $app->make(BloqueosProcessService::class),
                $parameters['nroLiqui'] ?? throw new \InvalidArgumentException('nroLiqui es requerido')
            );
        });

        // $this->app->singleton(SicossExportServiceOld::class, function ($app) {
        //     return new SicossExportServiceOld();
        // });

        $this->app->bind(Dh16RepositoryInterface::class, Dh16Repository::class);

        $this->app->bind(OrdenesDescuentoTableService::class, function ($app) {
            return new OrdenesDescuentoTableService(
                new OrdenesDescuentoTableDefinition()
            );
        });

        $this->app->bind(RepEmbarazadaServiceInterface::class, RepEmbarazadaService::class);
        $this->app->bind(ExportServiceInterface::class, AfipMapucheExportService::class);
        $this->app->bind(AfipRelacionesActivasServiceInterface::class, AfipRelacionesActivasService::class);
        
        // Registrar servicios SICOSS
        $this->app->singleton(SicossExportService::class);
        $this->app->singleton(SicossTxtExportService::class);
        $this->app->singleton(SicossExcelExportService::class);
        $this->app->singleton(SicossReportExportService::class);
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
