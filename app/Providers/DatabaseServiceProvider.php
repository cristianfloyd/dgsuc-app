<?php

namespace App\Providers;

use App\Contracts\DatabaseOperationInterface;
use App\Repositories\DatabaseOperationRepository;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Registra los servicios en el contenedor.
     *
     * @return void
     */
    public function register(): void
    {
        // Registrar la implementación por defecto
        $this->app->bind(DatabaseOperationInterface::class, function ($app) {
            return new DatabaseOperationRepository();
        });

        // Registrar implementaciones específicas para conexiones nombradas
        $this->app->bind('database.operation.mapuche', function ($app) {
            return new DatabaseOperationRepository('mapuche');
        });
    }

    /**
     * Inicializa los servicios después del registro.
     *
     * @return void
     */
    public function boot(): void
    {

    }
}
