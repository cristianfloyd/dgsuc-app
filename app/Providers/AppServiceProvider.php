<?php

namespace App\Providers;

use app\Services\ProcesarLinea;
use App\Providers\LineProcessor;
use App\Services\WorkflowService;
use App\Models\AfipSicossDesdeMapuche;
use Illuminate\Support\ServiceProvider;
use App\Contracts\ProcesarLineaContract;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
