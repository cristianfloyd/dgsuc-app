<?php

namespace App\Providers;

use App\Services\EmbargoTableService;
use Illuminate\Support\ServiceProvider;

class EmbargoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(EmbargoTableService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Solo ejecutar si la aplicación está completamente booteada y configurada
        if ($this->app->isBooted() && $this->app->bound('config')) {
            try {
                $tableService = $this->app->make(EmbargoTableService::class);
                $tableService->ensureTableExists();
            } catch (\Exception $e) {
                // Log el error pero no fallar la aplicación
                if ($this->app->bound('log')) {
                    $this->app->make('log')->warning('EmbargoServiceProvider: Error al inicializar tabla', [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }
}
