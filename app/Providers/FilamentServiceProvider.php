<?php

namespace App\Providers;

use App\Services\DatabaseConnectionService;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class FilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DatabaseConnectionService::class, function () {
            return new DatabaseConnectionService();
        });
    }

    public function boot(): void
    {
        // Registrar el selector de conexión de BD en todos los paneles
        FilamentView::registerRenderHook(
            PanelsRenderHook::TOPBAR_END,
            fn (): string => Blade::render('@livewire(\'database-connection-selector-badge\')')
        );

        // Registrar el renderHook para el footer
        FilamentView::registerRenderHook(
            PanelsRenderHook::FOOTER,
            fn (): string => Blade::render('@livewire(\'components.filament-footer\')')
        );
    }
}
