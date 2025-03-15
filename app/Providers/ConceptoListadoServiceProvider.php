<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Reportes\ConceptoListado;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Services\ConceptoListado\ConceptoListadoSyncService;
use App\Services\ConceptoListado\ConceptoListadoQueryService;
use App\Services\ConceptoListado\ConceptoListadoServiceInterface;

class ConceptoListadoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar el servicio de consulta
        $this->app->singleton(ConceptoListadoQueryService::class, function ($app) {
            return new ConceptoListadoQueryService();
        });

        // Registrar el servicio de sincronización
        $this->app->singleton(ConceptoListadoSyncService::class, function ($app) {
            return new ConceptoListadoSyncService(
                $app->make(ConceptoListado::class),
                $app->make(PeriodoFiscalService::class)
            );
        });

        // Binding condicional para la interfaz
        // Por defecto, cuando se solicite la interfaz, se entregará el servicio de consulta
        $this->app->bind(ConceptoListadoServiceInterface::class, ConceptoListadoQueryService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Configuraciones adicionales si son necesarias
    }
}
