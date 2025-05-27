<?php

namespace App\Providers;

use App\Services\WorkflowService;
use App\Listeners\JobFailedListener;
use App\Services\SicossExportService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use App\Services\RepEmbarazadaService;
use Illuminate\Queue\Events\JobFailed;
use App\Listeners\JobProcessedListener;
use Illuminate\Support\ServiceProvider;
use App\Repositories\BloqueosRepository;
use App\Contracts\ExportServiceInterface;
use Illuminate\Queue\Events\JobProcessed;
use App\Services\AfipMapucheExportService;
use App\Services\Reportes\BloqueosService;
use App\Contracts\WorkflowServiceInterface;
use App\Repositories\Mapuche\Dh16Repository;
use App\Services\AfipRelacionesActivasService;
use App\Services\OrdenesDescuentoTableService;
use App\Jobs\Middleware\InspectJobDependencies;
use App\Services\Imports\BloqueosImportService;
use App\Contracts\RepEmbarazadaServiceInterface;
use App\Repositories\BloqueosRepositoryInterface;
use App\Services\Reportes\BloqueosCleanupService;
use App\Services\Reportes\BloqueosProcessService;
use App\Services\Reportes\BloqueosHistorialService;
use App\Repositories\Mapuche\Dh16RepositoryInterface;
use App\Repositories\Mapuche\ConceptosTotalesRepository;
use App\Contracts\Tables\OrdenesDescuentoTableDefinition;
use App\Services\Reportes\BloqueosArchiveOrchestratorService;
use App\Services\Reportes\Interfaces\BloqueosServiceInterface;
use App\Services\Contracts\AfipRelacionesActivasServiceInterface;
use App\Repositories\Interfaces\ConceptosTotalesRepositoryInterface;
use App\Services\Reportes\Interfaces\BloqueosCleanupServiceInterface;
use App\Services\Reportes\Interfaces\BloqueosHistorialServiceInterface;
use App\Services\Reportes\Interfaces\BloqueosArchiveOrchestratorInterface;

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

        $this->app->singleton(SicossExportService::class, function ($app) {
            return new SicossExportService();
        });

        $this->app->bind(Dh16RepositoryInterface::class, Dh16Repository::class);

        $this->app->bind(OrdenesDescuentoTableService::class, function ($app) {
            return new OrdenesDescuentoTableService(
                new OrdenesDescuentoTableDefinition()
            );
        });

        $this->app->bind(RepEmbarazadaServiceInterface::class, RepEmbarazadaService::class);
        $this->app->bind(ExportServiceInterface::class, AfipMapucheExportService::class);
        $this->app->bind(AfipRelacionesActivasServiceInterface::class, AfipRelacionesActivasService::class);
        $this->app->bind(BloqueosRepositoryInterface::class, BloqueosRepository::class);
        $this->app->bind(ConceptosTotalesRepositoryInterface::class,ConceptosTotalesRepository::class
        );

        // Servicios de archivado de bloqueos
        $this->app->bind(BloqueosHistorialServiceInterface::class, BloqueosHistorialService::class);
        $this->app->bind(BloqueosCleanupServiceInterface::class, BloqueosCleanupService::class);
        $this->app->bind(BloqueosArchiveOrchestratorInterface::class, BloqueosArchiveOrchestratorService::class);
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

        // Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
        //     $event->extendSocialite('azure', \SocialiteProviders\Azure\Provider::class);
        // });
    }
}
